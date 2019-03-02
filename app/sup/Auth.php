<?php declare(strict_types=1);

namespace SUP;

define("ROLE_DISABLED", -1);
define("ROLE_STUDENT", 0);
define("ROLE_TEACHER", 1);
define("ROLE_ADMIN", 2);

class Auth
{
    private $logged = false;
    private $classMap = [
        'IA' => [1, 'A'],
        'IB' => [1, 'B'],

        'IIA' => [2, 'A'],
        'IIB' => [2, 'B'],

        'IIIA' => [3, 'A'],
        'IIIB' => [3, 'B'],

        'IVA' => [4, 'A'],
        'IVB' => [4, 'B'],

        'VA' => [5, 'A'],
        'VB' => [5, 'B'],
        '1C' => [5, 'C'],

        'VIA' => [6, 'A'],
        'VIB' => [6, 'B'],
        '2C'  => [6, 'C'],

        'VIIA' => [7, 'A'],
        'VIIB' => [7, 'B'],
        '3C'   => [7, 'C'],

        'VIIIA' => [8, 'A'],
        'VIIIB' => [8, 'B'],
        '4C'    => [8, 'C'],
    ];


    protected $user;
    protected $container;
    protected $db;

    public function __construct(\Slim\Container $container)
    {
        
        $this->container = $container;
        $this->db = $container->db;

        $this->getINIConfig();

        //$this->ensureAdmin();
        $this->loginFromSession();
    }

    private function getINIConfig() {
        
        $config = parse_ini_file(__DIR__ . '/../../config.ini', true);

        $this->ldapConfig = $config['ldap'];
    }

    public function loginFromSession():bool
    {
        if (array_key_exists('token', $_SESSION) && is_string($_SESSION['token'])) {
            $user = $this->db->get('users', ['id [Int]', 'roles [Object]'], ['token' => $_SESSION['token']]);
            if (!is_array($user) || $user['roles'] == [-1]) {
                $this->logout();
                return false;
            } else {
                $this->logged = true;
                $this->user = $this->container->factory->userFromID($user['id']);
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

        // ldap connect and replicate to DB

        $ldap = ldap_connect($this->ldapConfig['server'], intval($this->ldapConfig['port']));
        $ldaprdn = $username . '@' . $this->ldapConfig['domain'];

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $ldaprdn, $password);

        if (!$bind) {
            return false;
        }

        $filter="(sAMAccountName=$username)";
        $result = ldap_search($ldap,$this->ldapConfig['dc'], $filter);
        $info = ldap_get_entries($ldap, $result);

        if ($info['count'] != 1) {
            return false;
        }

        $givenname = $info[0]['givenname'][0];
        $surname   = $info[0]['sn'][0];
        $userDN    = explode(',', $info[0]['distinguishedname'][0]);

        if ($userDN[1] == 'OU=Leaders' || $userDN[1] == 'CN=Users') {
            $roles = [ROLE_STUDENT, ROLE_TEACHER, ROLE_ADMIN];
            $class = '';
            $year  = 0;
        } else if ($userDN[1] == 'OU=Teachers') {
            $roles = [ROLE_STUDENT, ROLE_TEACHER];
            $class = '';
            $year  = 0;
        } else if ($userDN[2] == 'OU=Students') {
            $roles     = [ROLE_STUDENT];
            $classInfo = strtoupper(explode('=', $userDN[1])[1]);
            $year      = $this->classMap[$classInfo][0];
            $class     = $this->classMap[$classInfo][1];
        }

        @ldap_close($ldap);

        if (!$this->db->has('users', ['uname' => $username])) {
            // replicate
            $id = $this->generateID(10000000, 99999999);

            $this->db->insert('users', [
                'id' => $id,
                'uname' => $username,
                'roles' => $roles,
                'activeRole' => max($roles)
            ]);

            $this->db->insert('userinfo', [
                'id' => $id,
                'surname' => $surname,
                'givenname' => $givenname,
                'class' => $class,
                'year' => $year
            ]);
        }

        $id = $this->db->get('users', ['id'], ['uname' => $username])['id'];

        $this->db->update('userinfo', [
            'surname' => $surname,
            'givenname' => $givenname,
            'class' => $class,
            'year' => $year
        ], [
            'id' => $id
        ]);
        
        $userinfo = $this->db->get('users', [
            'id [Int]',
            'roles [Object]',
        ], ['uname' => $username]);
        
        if (in_array(ROLE_DISABLED, $userinfo['roles'])) {
            return false;
        }
        
        $token = $this->generateToken();
        
        $_SESSION['token'] = $token;
        $this->db->update('users', [
            'token' => $token,
            'lastActive' => time()
        ], [
            'id' => $userinfo['id']
        ]);

        $this->logged = true;
        $this->user = (new User($this->container))->createfromDB($userinfo['id']);
        return true;
    }

    private function ldapConnect() {

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

    /*
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

    */

}
