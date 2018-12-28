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
            "name" => ["users", "userinfo"]
        ]]])) {
            $this->seed();
        }
    }

    function seed() {
        $this->db->query("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            name TEXT,
            pass TEXT,
            token TEXT NULL,
            role TEXT DEFAULT '-1',
            activeRole INTEGER,
            lastActive INTEGER NULL
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS userinfo (
            id INTEGER,
            surname TEXT NULL,
            givenname TEXT NULL,
            class TEXT NULL
        );");
        
        if (!$this->db->has("users", ["name" => "admin"])) {
            $this->db->insert("users", [
                "id"   => "1",
                "name" => "admin", 
                "pass" => password_hash("admin", PASSWORD_DEFAULT),
                "activeRole" => 2,
                "role" => '0,1,2'
            ]);
        }
        
    }
}
