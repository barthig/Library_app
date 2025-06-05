<?php
namespace App\Controllers;

class BaseController
{
    protected function startSessionIfNeeded(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    protected function checkAuth(string $requiredRole = null): void
    {
        $this->startSessionIfNeeded();
        if (empty($_SESSION['isLogged']) || $_SESSION['isLogged'] !== true) {
            header('Location: /login');
            exit;
        }

        if ($requiredRole && (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole)) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }
    }
}
