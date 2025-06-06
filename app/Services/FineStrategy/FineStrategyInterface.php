<?php

namespace App\Services\FineStrategy;

interface FineStrategyInterface
{
    /**
     * Calculates the fine based on the number of overdue days.
     * @param int $daysOverdue
     * @return float
     */
    public function calculate(int $daysOverdue): float;
}
