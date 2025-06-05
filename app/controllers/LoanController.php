<?php
namespace App\Controllers;
require_once __DIR__ . '/../Services/LoanService.php';
require_once __DIR__ . '/../Repositories/LoanRepository.php';
require_once __DIR__ . '/../Repositories/MemberRepository.php';
require_once __DIR__ . '/../Repositories/BookRepository.php';
require_once __DIR__ . '/../Factories/FineStrategyFactory.php';

use App\Services\LoanService;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\Interfaces\MemberRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Controllers\BaseController;

class LoanController extends BaseController
{
    protected LoanService $loanService;
    protected LoanRepositoryInterface $loanRepo;
    protected MemberRepositoryInterface $memberRepo;
    protected BookRepositoryInterface $bookRepo;

    public function __construct(LoanService $loanService, LoanRepositoryInterface $loanRepo, MemberRepositoryInterface $memberRepo, BookRepositoryInterface $bookRepo)
    {
        $this->loanService = $loanService;
        $this->loanRepo    = $loanRepo;
        $this->memberRepo  = $memberRepo;
        $this->bookRepo    = $bookRepo;
    }

    /**
     * Displays a list of all loans (together with Member and Book objects).
     */
    public function index()
    {
        $this->checkAuth();

        // 1. Fetch all loans
        $loans = $this->loanRepo->findAll();

        // 2. For each Loan load the related Member and Book
        foreach ($loans as $loan) {
            // fetch the member by member_id
            $member = $this->memberRepo->findById($loan->getMemberId());
            if ($member) {
                $loan->setMember($member);
            }
            // fetch the book by book_id
            $book = $this->bookRepo->findById($loan->getBookId());
            if ($book) {
                $loan->setBook($book);
            }
        }

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/loans/index.php';
    }

    /**
     * Loan creation form.
     */
    public function createForm()
    {
        $this->checkAuth('admin');

        $members = $this->memberRepo->findAll();
        $books   = $this->bookRepo->findAll();

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/loans/create.php';
    }

    /**
     * Saves a new loan.
     */
    public function store()
    {
        $this->checkAuth('admin');

        $memberId = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
        $bookId   = filter_input(INPUT_POST, 'book_id',   FILTER_SANITIZE_NUMBER_INT);
        $dueDate  = filter_input(INPUT_POST, 'due_date',  FILTER_UNSAFE_RAW);

        try {
            $this->loanService->createLoan($memberId, $bookId, $dueDate);
            header('Location: /loans');
            exit;
        } catch (\Exception $e) {
            // Store the error in the session so it can be shown on the form page
            $_SESSION['errors'] = [$e->getMessage()];
            header('Location: /loans/create');
            exit;
        }
    }

    /**
     * Book return form.
     */
    public function returnForm(int $loanId)
    {
        $this->checkAuth();

        $loan = $this->loanRepo->findById($loanId);
        if (!$loan) {
            header("HTTP/1.0 404 Not Found");
            echo "Loan not found.";
            exit;
        }

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/loans/return.php';
    }

    /**
     * Handles returning a book (POST).
     */
    public function returnLoan(int $loanId)
    {
        $this->checkAuth();

        $returnDate = filter_input(INPUT_POST, 'return_date', FILTER_UNSAFE_RAW);

        try {
            $this->loanService->returnLoan($loanId, $returnDate);
            header('Location: /loans');
            exit;
        } catch (\Exception $e) {
            // Store the error in the session to display it again on the return form
            $_SESSION['errors'] = [$e->getMessage()];
            header("Location: /loans/{$loanId}/return");
            exit;
        }
    }

    /**
     * Shows the loan edit form (changing the due date).
     */
    public function editForm(int $loanId)
    {
        $this->checkAuth('admin');

        $loan = $this->loanRepo->findById($loanId);
        if (!$loan) {
            header("HTTP/1.0 404 Not Found");
            echo "Loan not found.";
            exit;
        }

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/loans/edit.php';
    }

    /**
     * Updates a loan (currently only the due_date).
     */
    public function update(int $loanId)
    {
        $this->checkAuth('admin');

        $dueDate = filter_input(INPUT_POST, 'due_date', FILTER_UNSAFE_RAW);

        $loan = $this->loanRepo->findById($loanId);
        if (!$loan) {
            header("HTTP/1.0 404 Not Found");
            echo "Loan not found.";
            exit;
        }

        $loan->setDueDate($dueDate);

        try {
            $this->loanRepo->update($loan);
            header('Location: /loans');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = [$e->getMessage()];
            header("Location: /loans/{$loanId}/edit");
            exit;
        }
    }

    /**
     * Shows the loan history for a given member,
     * attaching Member and Book objects to each Loan.
     */
    public function history(int $memberId)
    {
        $this->checkAuth();

        $member = $this->memberRepo->findById($memberId);
        if (!$member) {
            header("HTTP/1.0 404 Not Found");
            echo "Member not found.";
            exit;
        }

        // Fetch all loans for the given member
        $history = $this->loanRepo->findByMember($memberId);

        // For each loan load Member and Book objects
        foreach ($history as $loan) {
            // Since we already have $member, assign it without another query:
            $loan->setMember($member);

            $book = $this->bookRepo->findById($loan->getBookId());
            if ($book) {
                $loan->setBook($book);
            }
        }

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/loans/history.php';
    }
}
