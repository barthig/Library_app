<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index(): void
    {
        include __DIR__ . '/../views/home/index.php';
    }
}
