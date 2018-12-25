<?php

define("ROLE_STUDENT", 0);
define("ROLE_TEACHER", 1);
define("ROLE_ADMIN", 2);

class auth {
    
    protected $container;
    protected $db;
    public $user;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->db = $this->container->db;
        
        if (
            is_string(@$_SESSION['token']) && 
            strlen(@$_SESSION['token']) == 8 &&
            $this->db->has("users", ["token" => $_SESSION['token']])
        ) {
            $info = $this->db->get("users", "*", ["token" => $_SESSION['token']]);
            $this->user = new user(true);
            $this->addUserInfo($info);
            $this->addUserAttribs($this->db->get("userinfo", "*", ["id" => $info['id']]));
        } else {
            $this->user = new user(false);
        }
    }

    private function addUserInfo($info) {
        $this->user->setInfo($info['id'], $info['name'], $info['role'], intVal($info['activeRole']));
    }

    private function addUserAttribs($attribs) {
        if (!is_array($attribs))
            return false;
        foreach (['givenname', 'surname', 'class'] as $val)
            if (array_key_exists($val, $attribs))
                $this->user->setAttrib($val, $attribs[$val]);
    }

    function login($id, $pass) {
        if ($this->user->logged())
            return false;
        
        if ($info = $this->db->get("users", "*", ["name" => $id])) {
            if (password_verify($pass, $info['pass'])) {
                do {$token = bin2hex(\openssl_random_pseudo_bytes(4));}
                while ($this->db->has("users", ["token" => $token]));
                
                $_SESSION['token'] = $token;
                $this->db->update("users", ["token" => $token], ["name" => $id]);
                
                $this->user->login();
                $this->addUserInfo($info);
                $this->addUserAttribs($this->db->get("userinfo", "*", ["id" => $info['id']]));

                return true;
            }
        }

        return false;
    }

    function logout() {
        if (!$this->user->logged())
            return false;
        
        $this->db->update("users", ["token" => null], ["token" => $_SESSION['token']]);
        unset($_SESSION['token']);
        
        $this->user->logout();
        
        return true;
    }

    function register($name, $pass, $roles) {
        do {
            $id = mt_rand(100, 999);
        } while ($this->db->has("users", ["id" => $id]));

        $hash = \password_hash($pass, \PASSWORD_DEFAULT);
        $this->db->insert("users", [
            "id" => $id,
            "name" => $name,
            "pass" => $hash,
            "role" => $this->encodeRole($roles)
        ]);
        return true;
    }

    function changePass($pass) {
        if (!$this->user->logged())
            return false;
        
        $this->db->update("users", ["pass" => password_hash($pass, PASSWORD_DEFAULT)], ["token" => $_SESSION['token']]);
        return true;
    }

    function checkPassword($pass) {
        if (!$this->user->logged())
            return false;
        
        return password_verify($pass, $this->db->get("users", "pass", ["token" => $_SESSION['token']]));
    }

    function changeRole($role) {
        if (!$this->user->changeRole($role)) return false;
        $this->db->update('users', ['activeRole' => $role], ["token" => $_SESSION['token']]);
        return true;
    }

    function changeAttrib($attrib, $val) {
        $this->user->setAttrib($attrib, $val);
        
        if (!$this->db->has("userinfo", ["id" => $this->user->getInfo("id")]))
            $this->db->insert("userinfo", [$attrib => $val, "id" => $this->user->getInfo('id')]);
        else
            $this->db->update("userinfo", [$attrib => $val], ["id" => $this->user->getInfo('id')]);
    }

    private function encodeRole($roles) {
        if (!is_array($roles))
            $roles = [$roles];
        return implode(",", $roles);
    }
}

class user {
    private $logged = false;
    private $props = [
        "id" => null,
        "name" => null,
        "roles" => null,
        "activeRole" => null
    ];

    private $attribs = [
        "givenname" => null,
        "surname" => null,
        "class" => null
    ];

    function __construct($logged = false) {
        $this->logged = $logged;
    }

    private function decodeRole($roles) {
        return array_map('intval', explode(",", $roles));
    }

    function setInfo($id, $name, $roles, $activeRole = null) {
        $this->props['id']    = $id;
        $this->props['name']  = $name;
        $this->props['roles'] = $this->decodeRole($roles);
        if (is_null($activeRole))
            $this->props['activeRole'] = max($this->decodeRole($roles));
        else
            $this->props['activeRole'] = $activeRole;
    }

    function changeRole($role) {

        if (!is_array(@$this->props['roles'])) return false;
        if (!in_array($role, $this->props['roles'])) return false;
        $this->props['activeRole'] = $role;

        return true;
    }

    function getInfo($field) {
        if (!array_key_exists($field, $this->props))
            throw new \Exception("Property " . $field . " in class user not found");
        
        return $this->props[$field];
    }

    function level($level) {

        if (!$this->logged) return false;
        if (is_null($this->props['activeRole'])) return false;
        
        if (is_array($level))
            return in_array($this->props['activeRole'], $level);
        else 
            return $level == $this->props['activeRole'];
        
    }

    function login() {$this->logged = true;}
    function logout() {$this->logged = false;}
    function logged() {return $this->logged;}

    function getAttrib($attrib) {
        if (!array_key_exists($attrib, $this->attribs))
            throw new \Exception("Attribute " . $attrib . " in class user not found");
        
        return $this->attribs[$attrib];
    }

    function setAttrib($attrib, $val) {
        if (!array_key_exists($attrib, $this->attribs))
            throw new \Exception("Attribute " . $attrib . " in class user not found");
        
        $this->attribs[$attrib] = $val;
    }

    function getName() {
        $ret = $this->attribs['givenname'] . " " . $this->attribs['surname'];
        if ($ret == " ")
            return null;
        return $ret;
    }
}