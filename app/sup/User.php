<?php

namespace sup;

class User {

    protected $db;
    protected $id;

    protected $attributes;

    function __construct(\Slim\Container $container) {
        $this->attributes = new Attributes;
        $this->db = $container->db;
    }

    function create(int $id):self {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    function createfromDB(int $id):?self {
        $userinfo = $this->db->get('users', ['[>]userinfo' => 'id'], [
            'users.id [Int]',
            'users.uname [String]',
            'users.token [String]',
            'name' => [
                'userinfo.givenname(given) [String]',
                'userinfo.surname(sur) [String]'
            ],
            'userinfo.class [String]',
            'users.roles [Object]',
            'users.activeRole [Int]',
            'users.lastActive [Int]'
        ], ['id' => $id]);

        if ($userinfo == false)
            return null;

        $this->id = $id;

        $clone = clone $this;
        $clone->attributesFromArray($userinfo);
        return $clone;

    }

    function withAttribute(string $name, $value):self {
        if (!is_numeric($this->id))
            throw new \Exception('User not created');

        $clone = clone $this;

        if (in_array($name, ['id', 'uname', 'token', 'roles', 'activeRole', 'lastActive']))
            $this->db->update('users', [$name => $value], ['id' => $this->id]);
        else if ($this->db->has('userinfo', ['id' => $this->id]))
            $this->db->update('userinfo', [$name => $value], ['id' => $this->id]);
        else
            $this->db->insert('userinfo', [$name => $value, 'id' => $this->id]);

        $clone->attributes->set($name, $value);
        return $clone;
    }

    function getAttribute(string $name) {
        return $this->attributes->get($name);
    }

    function getAttributes():Attributes {
        return $this->attributes->asArray();
    }

    function getName():?string {
        if (!$this->attributes->has('name'))
            return null;
        
        return implode(' ', $this->attributes->get('name'));
    }

    function getID() {
        return $this->id;
    }

    function is(int $level):bool {
        return $this->attributes->get('activeRole') == $level;
    }

    function canBecome(int $level):bool {
        if (!$this->attributes->has('roles'))
            return false;
        
        return in_array($level, $this->attributes->get('roles'));
    }

    function become(int $level):bool {
        if (!$this->canBecome($level))
            return false;
        
        $this->withAttribute('activeRole', $level);
        return true;
    }

    private function attributesFromArray(array $array) {
        foreach ($array as $name => $value)
            $this->attributes->set($name, $value);
    }
    
}
