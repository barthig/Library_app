<?php

namespace App\Services;

require_once __DIR__ . '/../Factories/FineStrategyFactory.php';

use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\MemberRepositoryInterface;
use App\Factories\FineStrategyFactory;
use App\Models\Loan;
use PDO;
use Exception;

class LoanService
{
    protected LoanRepositoryInterface $loanRepo;
    protected BookRepositoryInterface $bookRepo;
    protected MemberRepositoryInterface $memberRepo;
    protected $db; // PDO connection used for transactions

    public function __construct(LoanRepositoryInterface $loanRepo, BookRepositoryInterface $bookRepo, MemberRepositoryInterface $memberRepo)
    {
        $this->loanRepo   = $loanRepo;
        $this->bookRepo   = $bookRepo;
        $this->memberRepo = $memberRepo;
        $this->db         = \App\Models\Database::getConnection();
    }

    /**
     * Creates a new loan:
     * 1) Checks if the member exists.
     * 2) Checks if the book exists and is available (available_copies > 0).
     * 3) Inserts a row into the loans table.
     *    (Changing available_copies is handled by a database trigger.)
     *
     * All operations are executed in a single transaction.
     *
     * @param int    $memberId
     * @param int    $bookId
     * @param string $dueDate   (format 'YYYY-MM-DD')
     * @throws Exception
     */
    public function createLoan(int $memberId, int $bookId, string $dueDate)
    {
        $member = $this->memberRepo->findById($memberId);
        if (!$member) {
            throw new Exception("Member not found (ID: $memberId).");
        }

        $book = $this->bookRepo->findById($bookId);
        if (!$book) {
            throw new Exception("Book not found (ID: $bookId).");
        }

        if ($book->getAvailableCopies() < 1) {
            throw new Exception("No available copies for this book.");
        }

        try {
            $this->db->beginTransaction();

            $loan = new Loan([
                'member_id'   => $memberId,
                'book_id'     => $bookId,
                'loan_date'   => date('Y-m-d'),
                'due_date'    => $dueDate,
                'fine_amount' => 0.00,
            ]);
            $this->loanRepo->save($loan);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Handles returning a book:
     * 1) Checks whether the loan exists.
     * 2) Calculates the number of overdue days.
     * 3) Retrieves the strategy (e.g., from settings, default 'daily').
     * 4) Updates the loans row (return_date, fine_amount).
     *    (Changing available_copies is handled by a database trigger.)
     *
     * @param int    $loanId
     * @param string $returnDate (format 'YYYY-MM-DD')
     * @throws Exception
     */
    public function returnLoan(int $loanId, string $returnDate)
    {
        $loan = $this->loanRepo->findById($loanId);
        if (!$loan) {
            throw new Exception("Loan not found (ID: $loanId).");
        }

        if ($loan->getReturnDate() !== null) {
            throw new Exception("This loan was already returned.");
        }

        $book = $this->bookRepo->findById($loan->getBookId());
        if (!$book) {
            throw new Exception("Book not found (ID: {$loan->getBookId()}).");
        }

        $dueTs      = strtotime($loan->getDueDate());
        $returnTs   = strtotime($returnDate);
        $daysOverdue = 0;
        if ($returnTs > $dueTs) {
            $daysOverdue = intval(($returnTs - $dueTs) / 86400);
        }

        $strategy = FineStrategyFactory::make('daily');
        $fine = $loan->calculateFine($daysOverdue, $strategy);

        try {
            $this->db->beginTransaction();

            $loan->setReturnDate($returnDate);
            $loan->setFineAmount($fine);
            $this->loanRepo->update($loan);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
