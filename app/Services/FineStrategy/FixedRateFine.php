<?php
// Path: app/Services/FineStrategy/FixedRateFine.php
namespace App\Services\FineStrategy;

class FixedRateFine implements FineStrategyInterface
{
    /**
     * Assume the fine is a fixed 10 PLN regardless of the number of overdue days.
     */
    protected $fixedAmount = 10.00;

    public function calculate(int $daysOverdue): float
    {
        return ($daysOverdue > 0) ? $this->fixedAmount : 0.00;
    }
}
