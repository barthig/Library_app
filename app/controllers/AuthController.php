<?php

declare(strict_types=1);

namespace App\Controllers;


use App\Repositories\Interfaces\MemberRepositoryInterface;

class AuthController
{
    private MemberRepositoryInterface $memberRepo;

    public function __construct(MemberRepositoryInterface $memberRepo)
    {
        $this->memberRepo = $memberRepo;
    }

    /**
     * Displays the login form.
     */
    public function showLoginForm(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Retrieve any error message from the session
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        // Path to view: app/views/auth/login.php
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Handles the login POST request.
     */
    public function login(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Retrieve form data (assuming fields 'username' and 'password')
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Simple validation: fields must not be empty
        if ($username === '' || $password === '') {
            $_SESSION['auth_error'] = 'Please fill in all fields.';
            header('Location: /login');
            exit;
        }

        // Look up the user by username
        $member = $this->memberRepo->findByUsername($username);

        if ($member === null) {
            // No user with such username
            $_SESSION['auth_error'] = 'Invalid username or password.';
            header('Location: /login');
            exit;
        }

        // Verify the password (assuming getPassword() returns a hash from the database)
        if (!password_verify($password, $member->getPasswordHash())) {
            $_SESSION['auth_error'] = 'Invalid username or password.';
            header('Location: /login');
            exit;
        }

        // Login successful – storing data in session
        $_SESSION['user_id']   = $member->getId();
        $_SESSION['username']  = $member->getUsername();
        $_SESSION['role']      = $member->getRole();
        $_SESSION['isLogged']  = true;

        if ($member->getRole() === 'admin') {
            header('Location: /admin');
        } else {
            header('Location: /books');
        }
        exit;
    }

    /**
     * Logs out the user.
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Remove all session data
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        header('Location: /login');
        exit;
    }

    /**
     * Displays the registration form.
     */
    public function showRegisterForm(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        require_once __DIR__ . '/../views/auth/register.php';
    }

    /**
     * Handles user registration.
     */
    public function register(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $firstName  = trim((string) ($_POST['first_name']  ?? ''));
        $lastName   = trim((string) ($_POST['last_name']   ?? ''));
        $email      = trim((string) ($_POST['email']       ?? ''));
        $username   = trim((string) ($_POST['username']    ?? ''));
        $plainPass  = (string) ($_POST['password']        ?? '');
        $plainRepeat = (string) ($_POST['password_repeat'] ?? '');

        $_SESSION['old_input'] = [
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $email,
            'username'    => $username,
        ];

        $errors = [];
        if ($firstName === '') {
            $errors[] = 'First name is required.';
        }
        if ($lastName  === '') {
            $errors[] = 'Last name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if ($username   === '') {
            $errors[] = 'Username is required.';
        }
        if ($plainPass  === '') {
            $errors[] = 'Password is required.';
        }
        if ($plainPass !== $plainRepeat) {
            $errors[] = 'Passwords do not match.';
        }
        if ($this->memberRepo->existsByEmail($email)) {
            $errors[] = 'Email is already taken.';
        }
        if ($this->memberRepo->existsByUsername($username)) {
            $errors[] = 'Username is already taken.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /auth/register');
            exit;
        }

        $passwordHash = password_hash($plainPass, PASSWORD_DEFAULT);
        $cardNumber = $this->memberRepo->getNextCardNumber();
        $member = new \App\Models\Member(
            null,
            $firstName,
            $lastName,
            $email,
            $cardNumber,
            $username,
            $passwordHash
        );
        $this->memberRepo->save($member);

        $_SESSION['flash'] = 'Registration successful. You can now log in.';
        header('Location: /login');
        exit;
    }

    /**
     * Displays forgot password form.
     */
    public function showForgotPasswordForm(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require_once __DIR__ . '/../views/auth/forgot_password.php';
    }

    /**
     * Processes forgot password request.
     */
    public function sendPasswordReset(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['flash'] = 'If the email exists in our system, a reset link has been sent.';
        header('Location: /auth/forgot-password');
        exit;
    }
}
