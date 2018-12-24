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
            "name" => ["users", "userinfo"]
        ]]])) {
            $this->seed();
        }
    }

    # auth

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

        # lists

        $this->db->query("CREATE TABLE IF NOT EXISTS lists_settings (
            active_version INTEGER NULL,
            open_editing INTEGER DEFAULT 0,
            open_validate INTEGER DEFAULT 0,
            open_drawing INTEGER DEFAULT 0
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS lists_versions (
            id INTEGER PRIMARY KEY,
            name TEXT
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS lists_books (
            id INTEGER PRIMARY KEY,
            name TEXT,
            author TEXT NULL,
            region INTEGER NULL,
            genere INTEGER NULL,
            version INTEGER
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS lists_regions (
            id INTEGER PRIMARY KEY,
            name TEXT,
            min INTEGER,
            max INTEGER,
            version INTEGER
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS lists_generes (
            id INTEGER PRIMARY KEY,
            name TEXT,
            min INTEGER,
            max INTEGER,
            version INTEGER
        );");

        $this->db->query("CREATE TABLE IF NOT EXISTS lists_main (
            id INTEGER PRIMARY KEY,
            user INTEGER,
            created INTEGER,
            state INTEGER DEFAULT 0,
            version INTEGER
        );"); // 0 - editing, 1 - sent, 2 - accepted

        $this->db->query("CREATE TABLE IF NOT EXISTS lists_lists (
            list INTEGER,
            book INTEGER
        );");
        
    }
}
