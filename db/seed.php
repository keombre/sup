<?php

namespace database;

class seed {
    
    protected $container;
    protected $db;

    private $baseSchema = [
        "modules" => [
            "id" => "INTEGER PRIMARY KEY",
            "name" => "TEXT",
            "version" => "TEXT",
            "baseVersion" => "TEXT",
            "active" => "INTEGER DEFAULT 0"
        ],
        "users" => [
            "id" => "INTEGER PRIMARY KEY",
            "uname" => "TEXT",
            "passw" => "TEXT",
            "token" => "TEXT NULL",
            "roles" => "TEXT",
            "activeRole" => "INTEGER",
            "lastActive" => "INTEGER NULL"
        ],
        "userinfo" => [
            "id" => "INTEGER",
            "surname" => "TEXT NULL",
            "givenname" => "TEXT NULL",
            "class" => "TEXT NULL"
        ]
    ];

    function __construct(\Slim\Container $container, $schema) {
        $this->container = $container;
        $this->db = $this->container->db;
        
        if (is_array($schema)) {
            $this->schema = $schema;
        } else {
            $this->schema = $this->baseSchema;
        }
    }

    function update() {
        $tables = $this->db->select('sqlite_master', 'name', ['type' => 'table']);

        foreach ($this->schema as $name => $schema) {
            if (in_array($name, $tables))
                continue;
            
            $this->populate($name, $schema);
        }
    }

    private function populate($name, $schema) {
        $fields = "";

        foreach ($schema as $column => $type) {
            $fields .= $this->db->quote($column) . " " . $type . ", ";
        }

        $fields = substr($fields, 0, -2);
        $name = $this->db->quote($name);

        $this->db->query("CREATE TABLE IF NOT EXISTS $name ( $fields )");
        
    }
}
