<?php declare(strict_types=1);

namespace SUP;

class Attributes
{
    protected $attributes = [];

    public function has(string $name):bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function get(string $name)
    {
        if (!$this->has($name)) {
            return null;
        }
        
        return $this->attributes[$name];
    }

    public function set(string $name, $value):void
    {
        $this->attributes[$name] = $value;
    }

    public function with(string $name, $value):self
    {
        $clone = clone $this;
        $clone->set($name, $value);
        return $clone;
    }

    public function asArray():array
    {
        return $this->attributes;
    }
}
