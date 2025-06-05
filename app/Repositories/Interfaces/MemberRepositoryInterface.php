<?php
namespace App\Repositories\Interfaces;

use App\Models\Member;

interface MemberRepositoryInterface
{
    public function findAll();
    public function findById($id);
    public function findByUsername($username);
    public function save($member);
    public function update($member);
    public function delete($id);
    public function existsByEmail($email);
    public function existsByUsername($username);
    public function getNextCardNumber();
}
