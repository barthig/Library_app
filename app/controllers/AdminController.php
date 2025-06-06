<?php

namespace App\Controllers;

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $this->checkAuth('admin');

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/admin/dashboard.php';
    }
}
