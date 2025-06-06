<?php

namespace App\Repositories;

require_once __DIR__ . '/../models/Database.php';

abstract class Repository
{
    protected $db;

    public function __construct()
    {
        $this->db = \App\Models\Database::getConnection();
    }

    abstract public function findById($id);
    abstract public function findAll();
    abstract public function save($entity);
    abstract public function update($entity);
    abstract public function delete($id);
}
