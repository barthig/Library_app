<?php
namespace App\Models;

class Loan {
    private ?int $id;
    private ?int $member_id;
    private ?int $book_id;
    private ?string $loan_date;
    private ?string $due_date;
    private ?string $return_date;
    private float $fine_amount;
    private $member;
    private $book;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->member_id = $data['member_id'] ?? null;
        $this->book_id = $data['book_id'] ?? null;
        $this->loan_date = $data['loan_date'] ?? date('Y-m-d');
        $this->due_date = $data['due_date'] ?? null;
        $this->return_date = $data['return_date'] ?? null;
        $this->fine_amount = isset($data['fine_amount']) ? (float)$data['fine_amount'] : 0.00;
    }

    public function getId(): ?int { return $this->id; }
    public function getMemberId(): ?int { return $this->member_id; }
    public function getBookId(): ?int { return $this->book_id; }
    public function getLoanDate(): ?string { return $this->loan_date; }
    public function getDueDate(): ?string { return $this->due_date; }
    public function getReturnDate(): ?string { return $this->return_date; }
    public function getFineAmount(): float { return $this->fine_amount; }
    public function setMemberId(?int $id): void { $this->member_id = $id; }
    public function setBookId(?int $id): void { $this->book_id = $id; }
    public function setLoanDate(?string $date): void { $this->loan_date = $date; }
    public function setDueDate(?string $date): void { $this->due_date = $date; }
    public function setReturnDate(?string $date): void { $this->return_date = $date; }
    public function setFineAmount(float $amount): void { $this->fine_amount = $amount; }
    public function setId(?int $id): void { $this->id = $id; }
    public function setMember($member) { $this->member = $member; }
    public function getMember() { return $this->member; }

    public function setBook($book) { $this->book = $book; }
    public function getBook() { return $this->book; }

    /**
     * Checks whether the loan is overdue.
     */
    public function isOverdue(): bool {
        if ($this->return_date !== null) {
            return false;
        }
        return (strtotime($this->due_date) < time());
    }

    /**
     * Calculates the fine using a strategy.
     * @param int $daysOverdue number of days overdue
     * @param \App\Services\FineStrategy\FineStrategyInterface $strategy
     * @return float
     */
    public function calculateFine(int $daysOverdue, \App\Services\FineStrategy\FineStrategyInterface $strategy): float {
        return $strategy->calculate($daysOverdue);
    }
}
