<?php
namespace App\Repositories\Interfaces;

use App\Models\Book;

interface BookRepositoryInterface
{
    public function findById($id);
    public function findAll();
    public function save($entity);
    public function update($entity);
    public function delete($id);
}
