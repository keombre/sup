<?php

namespace SUP;

define("ROLE_DISABLED", -1);
define("ROLE_STUDENT", 0);
define("ROLE_TEACHER", 1);
define("ROLE_ADMIN", 2);

class Auth
{
    private $logged = false;

    protected $user;
    protected $container;
    protected $db;

    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
        $this->db = $container->db;
        $this->ensureAdmin();
        $this->loginFromSession();
    }

    public function loginFromSession():bool
    {
        if (array_key_exists('token', $_SESSION) && is_string($_SESSION['token'])) {
            $id = $this->db->get('users', 'id', ['token' => $_SESSION['token']]);
            if ($id == false) {
                $this->logout();
                return false;
            } else {
                $this->logged = true;
                $this->user = (new User($this->container))->createFromDB($id);
                return true;
            }
        }
        return false;
    }

    public function login(string $username, string $password):bool
    {
        if ($this->logged) {
            return true;
        }
        
        $userinfo = $this->db->get('users', [
            'id [Int]',
            'roles [Object]',
            'passw [String]'
        ], ['uname' => $username]);
        
        if ($userinfo == false) {
            return false;
        }
        
        if (in_array(ROLE_DISABLED, $userinfo['roles'])) {
            return false;
        }
        
        if (!password_verify($password, $userinfo['passw'])) {
            return false;
        }
        
        $token = $this->generateToken();
        
        $_SESSION['token'] = $token;
        $this->db->update('users', ['token' => $token, 'lastActive' => time()], ['id' => $userinfo['id']]);

        $this->logged = true;
        $this->user = (new User($this->container))->createfromDB($userinfo['id']);
        return true;
    }

    public function logout():bool
    {
        if (!$this->logged) {
            return false;
        }
        
        $this->db->update('users', ['token' => null], ['token' => $_SESSION['token']]);
        unset($_SESSION['token']);
        $this->logged = false;
        
        return true;
    }

    public function register(string $username, string $password, array $roles):bool
    {
        if ($this->db->has('users', ['uname' => $username])) {
            return false;
        }
        
        $id = $this->generateID(1000, 99999999);
        $hash = \password_hash($password, \PASSWORD_DEFAULT);

        $this->db->insert('users', [
            'id' => $id,
            'uname' => $username,
            'passw' => $hash,
            'roles' => $roles,
            'activeRole' => max($roles)
        ]);
        return true;
    }
    
    public function checkPass(string $password, ?User $user = null):bool
    {
        if (is_null($user)) {
            $user = $this->user;
        }
        
        if (is_null($user)) {
            return false;
        }
        
        return password_verify($password, $this->db->get('users', 'passw', ['id' => $user->getID()]));
    }

    public function changePass(string $password, ?User $user = null):bool
    {
        if (is_null($user)) {
            $user = $this->user;
        }
        
        if (is_null($user)) {
            return false;
        }
        
        $this->db->update('users', [
            'passw' => password_hash($password, PASSWORD_DEFAULT)
        ], ['id' => $user->getID()]);

        return true;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function logged()
    {
        return $this->logged;
    }

    private function generateToken()
    {
        do {
            $token = bin2hex(\openssl_random_pseudo_bytes(10));
        } while ($this->db->has("users", ["token" => $token]));
        return $token;
    }

    public function generateID(int $rangeMin, int $rangeMax)
    {
        do {
            $id = mt_rand($rangeMin, $rangeMax);
        } while ($this->db->has("users", ["id" => $id]));
        return $id;
    }

    private function ensureAdmin()
    {
        if (!$this->db->has('users', ['id' => '1'])) {
            $this->db->insert('users', [
                'id' => 1,
                'uname' => 'admin',
                'passw' => password_hash('admin', PASSWORD_DEFAULT),
                'roles' => [0, 1, 2],
                'activeRole' => 2
            ]);
        }
    }
}
