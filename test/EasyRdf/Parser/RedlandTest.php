<?php
namespace EasyRdf\Parser;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or
 *    promote products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

use EasyRdf\Graph;
use EasyRdf\TestCase;

require_once dirname(dirname(__DIR__)).
             DIRECTORY_SEPARATOR.'TestHelper.php';

class RedlandTest extends TestCase
{
    /** @var Redland */
    private $parser;
    /** @var Graph */
    private $graph;
    private $rdf_data;

    public function setUp()
    {
        if (extension_loaded('redland')) {
            $this->parser = new Redland();
            $this->graph = new Graph();
            $this->rdf_data = readFixture('foaf.rdf');
        } else {
            $this->markTestSkipped("Redland PHP extension is not available.");
        }
    }

    public function testParseRdfXml()
    {
        $count = $this->parser->parse(
            $this->graph,
            $this->rdf_data,
            'rdfxml',
            'http://www.example.com/joe/foaf.rdf'
        );
        $this->assertSame(14, $count);

        $joe = $this->graph->resource('http://www.example.com/joe#me');
        $this->assertNotNull($joe);
        $this->assertClass('EasyRdf\Resource', $joe);
        $this->assertSame('http://www.example.com/joe#me', $joe->getUri());

        $name = $joe->get('foaf:name');
        $this->assertNotNull($name);
        $this->assertClass('EasyRdf\Literal', $name);
        $this->assertStringEquals('Joe Bloggs', $name);
        $this->assertSame('en', $name->getLang());
        $this->assertSame(null, $name->getDatatype());

        $foaf = $this->graph->resource('http://www.example.com/joe/foaf.rdf');
        $this->assertNotNull($foaf);
        $this->assertStringEquals("Joe Bloggs' FOAF File", $foaf->label());
    }

    public function testParseRdfXmlBnodeRemap()
    {
        $count = $this->parser->parse(
            $this->graph,
            "<rdf:RDF xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#' ".
            "         xmlns:foaf='http://xmlns.com/foaf/0.1/' >".
            "  <rdf:Description rdf:nodeID='bob'>".
            "    <foaf:name>Bob</foaf:name>".
            "  </rdf:Description>".
            "</rdf:RDF>",
            'rdfxml',
            'http://www.example.com/bob/foaf.rdf'
        );
        $this->assertSame(1, $count);

        $this->assertStringEquals(
            'Bob',
            $this->graph->get('_:genid1', 'foaf:name')
        );
    }
}
