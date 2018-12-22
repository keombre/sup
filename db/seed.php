<?php

namespace database;

class seed {
    
    protected $container;
    protected $db;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->db = $this->container->db;
    }

    function update() {
        if (!$this->container->db->has("sqlite_master", ["AND" => ["type" => "table", "OR" => [
            "name" => ["users", "userinfo", "books", "listgroups", "lists", "regions", "generes"]
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
            role TEXT DEFAULT '0',
            activeRole INTEGER NULL,
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

        $this->db->query("CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY,
            name TEXT,
            author TEXT NULL,
            region INTEGER NULL,
            genere INTEGER NULL
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS regions (
            id INTEGER PRIMARY KEY,
            name TEXT
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS generes (
            id INTEGER PRIMARY KEY,
            name TEXT
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS listgroups (
            id INTEGER PRIMARY KEY,
            user INTEGER,
            created INTEGER,
            state INTEGER DEFAULT 0
        );"); // 0 - editing, 1 - sent, 2 - accepted

        $this->db->query("CREATE TABLE IF NOT EXISTS lists (
            list INTEGER,
            book INTEGER
        );");
        
    }
}
