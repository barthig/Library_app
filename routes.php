<?php
// Path: routes.php
declare(strict_types=1);

require_once __DIR__ . '/app/Router.php';

use App\Router;

Router::get("/", "App\Controllers\HomeController", "index");

// AUTHENTICATION
Router::get('/login',   'App\Controllers\AuthController', 'showLoginForm');
Router::post('/login',  'App\Controllers\AuthController', 'login');
Router::post('/logout', 'App\Controllers\AuthController', 'logout');
Router::get('/auth/register', 'App\Controllers\AuthController', 'showRegisterForm');
Router::post('/auth/register', 'App\Controllers\AuthController', 'register');
Router::get('/auth/forgot-password', 'App\Controllers\AuthController', 'showForgotPasswordForm');
Router::post('/auth/forgot-password', 'App\Controllers\AuthController', 'sendPasswordReset');
Router::get('/admin', 'App\Controllers\AdminController', 'dashboard');

// BOOKS
Router::get('/books',             'App\Controllers\BookController', 'index');
Router::get('/books/create',      'App\Controllers\BookController', 'createForm');
Router::post('/books',            'App\Controllers\BookController', 'store');
Router::get('/books/{id}',        'App\Controllers\BookController', 'show');
Router::get('/books/{id}/edit',   'App\Controllers\BookController', 'editForm');
Router::post('/books/{id}/edit',  'App\Controllers\BookController', 'update');
Router::post('/books/{id}/delete','App\Controllers\BookController', 'delete');

// MEMBERS
Router::get('/members',             'App\Controllers\MemberController', 'index');
Router::get('/members/create',      'App\Controllers\MemberController', 'createForm');
Router::post('/members',            'App\Controllers\MemberController', 'store');
Router::get('/members/{id}',        'App\Controllers\MemberController', 'show');
Router::get('/members/{id}/edit',   'App\Controllers\MemberController', 'editForm');
Router::post('/members/{id}/edit',  'App\Controllers\MemberController', 'update');
Router::post('/members/{id}/delete','App\Controllers\MemberController', 'delete');

// AUTHORS
Router::get('/authors',             'App\Controllers\AuthorController', 'index');
Router::get('/authors/create',      'App\Controllers\AuthorController', 'createForm');
Router::post('/authors',            'App\Controllers\AuthorController', 'store');
Router::get('/authors/{id}',        'App\Controllers\AuthorController', 'show');
Router::get('/authors/{id}/edit',   'App\Controllers\AuthorController', 'editForm');
Router::post('/authors/{id}/edit',  'App\Controllers\AuthorController', 'update');
Router::post('/authors/{id}/delete','App\Controllers\AuthorController', 'delete');

// LOANS
Router::get('/loans',                     'App\Controllers\LoanController', 'index');
Router::get('/loans/create',              'App\Controllers\LoanController', 'createForm');
Router::post('/loans',                    'App\Controllers\LoanController', 'store');
Router::get('/loans/{loanId}/return',     'App\Controllers\LoanController', 'returnForm');
Router::post('/loans/{loanId}/return',    'App\Controllers\LoanController', 'returnLoan');
Router::get('/loans/{loanId}/edit',       'App\Controllers\LoanController', 'editForm');
Router::post('/loans/{loanId}/edit',      'App\Controllers\LoanController', 'update');
Router::get('/loans/history/{memberId}',  'App\Controllers\LoanController', 'history');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
Router::dispatch($uri);
