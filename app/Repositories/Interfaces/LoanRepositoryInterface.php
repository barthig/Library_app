<?php

namespace App\Repositories\Interfaces;

use App\Models\Loan;

interface LoanRepositoryInterface
{
    public function findById($id);
    public function findAll();
    public function findByMember($memberId);
    public function save($entity);
    public function update($entity);
    public function delete($id);
}
