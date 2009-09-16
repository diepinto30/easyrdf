<?php

require_once "EasyRdf/Namespace.php";

class EasyRdf_Resource
{
    /** The URI for this resource */
    private $_uri = null;
    
    /** Associative array of properties */
    private $_properties = array();
    
    /** Enable / disable PHP's magic __call() method */
    private static $_magicEnabled = true;


    public static function disableMagic()
    {
        self::$magicEnabled = false;
    }
    
    public static function enableMagic()
    {
        self::$magicEnabled = true;
    }
    
    // This shouldn't be called directly
    public function __construct($uri)
    {
        $this->_uri = $uri;
    }
    
    /** Returns the URI for the resource. */
    public function getUri()
    {
        return $this->_uri;
    }
    
    public function set($property, $value)
    {
        if ($property == null or $value == null) {
            return null;
        } else if (array_key_exists($property, $this->_properties)) {
            $values = $this->_properties[$property];
        } else {
            $values = array();
        }
        // Add to array of values, if it isn't already there
        if (!in_array($value, $values)) {
            array_push($values, $value);
        }
        return $this->_properties[$property] = $values;
    }

    public function get($property)
    {
        if (isset($this->_properties[$property])) {
            if (is_array($this->_properties[$property])) {
                $values = $this->_properties[$property];
                return $values[0];
            } else {
                return $this->_properties[$property];
            }
        } else {
            return null;
        }
    }
    
    public function all($property)
    {
        if (isset($this->_properties[$property])) {
            if (is_array($this->_properties[$property])) {
                return $this->_properties[$property];
            } else {
                return array($this->_properties[$property]);
            }
        } else {
            return array();
        }
    }
    
    public function join($property, $glue=' ')
    {
        return join($glue, $this->all($property));
    }
    
    public function isBnode()
    {
        if (substr($this->_uri, 0, 2) == '_:') {
            return true;
        } else {
            return false;
        }
    }
    
    # Return an array of this resource's types
    public function types()
    {
        return $this->all('rdf_type');
    }
    
    # Return the resource type as a single word (rather than a URI)
    public function type()
    {
        return $this->get('rdf_type');
    }
    
    # Return the namepace that this resource is part of
    public function ns()
    {
        return EasyRdf_Namespace::namespaceOfUri($this->_uri);
    }
    
    public function shorten()
    {
        return EasyRdf_Namespace::shorten($this->_uri);
    }
    
    public function label()
    {
        if ($this->get('rdfs_label')) {
            return $this->get('rdfs_label');
        } else if ($this->get('foaf_name')) {
            return $this->get('foaf_name');
        } else if ($this->get('dc_title')) {
            return $this->get('dc_title');
        } else {
            return EasyRdf_Namespace::shorten($this->_uri); 
        }
    }
    
    public function dump($html=true, $depth=0)
    {
        # FIXME: finish implementing this
        echo '<pre>';
        echo '<b>'.$this->getUri()."</b>\n";
        echo 'Class: '.get_class($this)."\n";
        echo 'Types: '.implode(', ', $this->types())."\n";
        echo "Properties:</i>\n";
        foreach ($this->_properties as $property => $values) {
            echo "  $property => \n";
            foreach ($values as $value) {
                echo "    $value\n";
            }
        }
        echo "</pre>";
    }

    
    public function __call($name, $arguments)
    {
        $method = substr($name, 0, 3);
        $property = strtolower(substr($name, 3, 1)) . substr($name, 4);
        
        switch ($method) {
          case 'get':
              return $this->get($property);
              break;
          
          case 'all':
              return $this->all($property);
              break;
        
          default:
              # FIXME: throw exception
              return null;
              break;
        }
    }
    
    public function __toString()
    {
        return $this->_uri;
    }
}

