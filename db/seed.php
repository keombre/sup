<?php

namespace database;

class seed {
    
    protected $container;
    protected $db;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->db = $this->container->db;
        $this->update();
    }

    function update() {
        if (!$this->db->has("sqlite_master", ["AND" => ["type" => "table", "OR" => [
            "name" => ["users", "userinfo", "modules"]
        ]]])) {
            $this->seed();
        }
    }

    function seed() {

        $this->db->query("CREATE TABLE IF NOT EXISTS modules (
            id INTEGER PRIMARY KEY,
            name TEXT,
            version TEXT,
            baseVersion TEXT,
            active INTEGER DEFAULT 0
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            uname TEXT,
            passw TEXT,
            token TEXT NULL,
            roles TEXT,
            activeRole INTEGER,
            lastActive INTEGER NULL
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS userinfo (
            id INTEGER,
            surname TEXT NULL,
            givenname TEXT NULL,
            class TEXT NULL
        );");
        
        if (!$this->db->has('users', ['uname' => 'admin'])) {
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
