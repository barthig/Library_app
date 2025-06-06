<?php

namespace App\Repositories\Interfaces;

use App\Models\Author;

interface AuthorRepositoryInterface
{
    public function findById($id);
    public function findAll();
    public function save($entity);
    public function update($entity);
    public function delete($id);
    public function findByName(string $fullName);
}
