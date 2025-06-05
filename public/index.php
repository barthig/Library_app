<?php
// Path: public/index.php

declare(strict_types=1);


// Load all initialization (e.g., database configuration, environment settings, etc.)
require __DIR__ . '/../app/bootstrap.php';

// Register routes and launch the Router
require __DIR__ . '/../routes.php';
