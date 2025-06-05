<?php
declare(strict_types=1);

require_once __DIR__ . '/models/Database.php';

require_once __DIR__ . '/models/Member.php';
require_once __DIR__ . '/models/Book.php';
require_once __DIR__ . '/models/Author.php';
require_once __DIR__ . '/models/Loan.php';

require_once __DIR__ . '/Repositories/Repository.php';
require_once __DIR__ . '/repositories/Interfaces/BookRepositoryInterface.php';
require_once __DIR__ . '/repositories/Interfaces/AuthorRepositoryInterface.php';
require_once __DIR__ . '/repositories/Interfaces/MemberRepositoryInterface.php';
require_once __DIR__ . '/repositories/Interfaces/LoanRepositoryInterface.php';
require_once __DIR__ . '/Repositories/MemberRepository.php';
require_once __DIR__ . '/Repositories/BookRepository.php'; 
require_once __DIR__ . '/Repositories/AuthorRepository.php';
require_once __DIR__ . '/Repositories/LoanRepository.php';
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/Container.php';

use App\Container;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\MemberRepositoryInterface;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\AuthorRepository;
use App\Repositories\BookRepository;
use App\Repositories\MemberRepository;
use App\Repositories\LoanRepository;
use App\Services\LoanService;

Container::bind(AuthorRepositoryInterface::class, fn() => new AuthorRepository());
Container::bind(BookRepositoryInterface::class, fn() => new BookRepository());
Container::bind(MemberRepositoryInterface::class, fn() => new MemberRepository());
Container::bind(LoanRepositoryInterface::class, fn() => new LoanRepository());
Container::bind(LoanService::class, function() {
    return new LoanService(
        Container::get(LoanRepositoryInterface::class),
        Container::get(BookRepositoryInterface::class),
        Container::get(MemberRepositoryInterface::class)
    );
});


