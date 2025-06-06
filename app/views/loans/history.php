<?php

?>
<h1>Loan History for <?= htmlspecialchars($member->getFirstName() . ' ' . $member->getLastName()) ?></h1>

<table>
    <thead>
        <tr>
            <th>Book ID</th>
            <th>Loan Date</th>
            <th>Due Date</th>
            <th>Return Date</th>
            <th>Fine (PLN)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($history)): ?>
            <?php foreach ($history as $loan): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($loan->getBookId()) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($loan->getLoanDate()) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($loan->getDueDate()) ?>
                    </td>
                    <td>
                        <?= $loan->getReturnDate()
                            ? htmlspecialchars($loan->getReturnDate())
                            : '-' ?>
                    </td>
                    <td>
                        <?= $loan->getFineAmount() !== null
                            ? number_format($loan->getFineAmount(), 2)
                            : '0.00' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No history found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<p>
    <a href="/loans">Back to Loans</a>
</p>