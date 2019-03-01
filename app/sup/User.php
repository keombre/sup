<?php

namespace SUP;

class User
{
    protected $db;
    protected $container;
    protected $id;

    protected $attributes;

    public function __construct(\Slim\Container $container)
    {
        $this->attributes = new Attributes;
        $this->container = $container;
        $this->db = $container->db;
    }

    public function asArray()
    {
        $ret = [
            "id" => $this->id,
            "attributes" => $this->attributes->asArray()
        ];
        return $ret;
    }

    public function create(int $id):self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function createfromDB(int $id):?self
    {
        $userinfo = $this->db->get('users', ['[>]userinfo' => 'id'], [
            'users.id [Int]',
            'users.uname [String]',
            'name' => [
                'userinfo.givenname(given) [String]',
                'userinfo.surname(sur) [String]'
            ],
            'userinfo.class [String]',
            'users.roles [Object]',
            'users.activeRole [Int]',
            'users.lastActive [Int]',
            'userinfo.year [Int]'
        ], ['id' => $id]);

        if ($userinfo == false) {
            return null;
        }

        $this->id = $id;

        $clone = clone $this;
        $clone->attributesFromArray($userinfo);
        return $clone;
    }

    public function withAttribute(string $name, $value):self
    {
        if (!is_numeric($this->id)) {
            throw new \Exception('User not created');
        }

        $clone = clone $this;

        if (in_array($name, ['id', 'uname', 'token', 'roles', 'activeRole', 'lastActive'])) {
            $this->db->update('users', [$name => $value], ['id' => $this->id]);
        } elseif ($this->db->has('userinfo', ['id' => $this->id])) {
            $this->db->update('userinfo', [$name => $value], ['id' => $this->id]);
        } else {
            $this->db->insert('userinfo', [$name => $value, 'id' => $this->id]);
        }

        $clone->attributes->set($name, $value);
        return $clone;
    }

    public function getAttribute(string $name)
    {
        return $this->attributes->get($name);
    }

    public function getAttributes():Attributes
    {
        return $this->attributes->asArray();
    }

    public function getName():?string
    {
        if (!$this->attributes->has('name')) {
            return null;
        }
        
        return implode(' ', $this->attributes->get('name'));
    }

    public function getLocalisedClass():?string {
        if (!$this->attributes->has('class') || !$this->attributes->has('year')) {
            return null;
        }

        return $this->container->lang->g(
            $this->getAttribute('year') . strtoupper($this->getAttribute('class')),
        'userClass');
    }

    public function getAnyName():string
    {
        $name = $this->getName();
        if (is_null($name) || $name == ' ') {
            $name = $this->getUname();
        }

        return $name;
    }

    public function getID()
    {
        return $this->id;
    }

    public function getUname():string
    {
        return $this->attributes->get('uname');
    }

    public function is(int $level):bool
    {
        return $this->attributes->get('activeRole') == $level;
    }

    public function canBecome(int $level):bool
    {
        if (!$this->attributes->has('roles')) {
            return false;
        }
        
        return in_array($level, $this->attributes->get('roles'));
    }

    public function become(int $level):bool
    {
        if (!$this->canBecome($level)) {
            return false;
        }
        
        $this->withAttribute('activeRole', $level);
        return true;
    }

    private function attributesFromArray(array $array)
    {
        foreach ($array as $name => $value) {
            $this->attributes->set($name, $value);
        }
    }
}
