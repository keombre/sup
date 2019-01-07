<?php

namespace SUP;

class Attributes {

    protected $attributes = [];

    function has(string $name):bool {
        return array_key_exists($name, $this->attributes);
    }

    function get(string $name) {
        if (!$this->has($name))
            return null;
        
        return $this->attributes[$name];
    }

    function set(string $name, $value) {
        $this->attributes[$name] = $value;
    }

    function with(string $name, $value):self {
        $clone = clone $this;
        $clone->set($name, $value);
        return $clone;
    }

    function asArray():array {
        return $this->attributes;
    }
}
