<?php
// Path: app/views/members/show.php
?>
<h1>Member Details</h1>

<p>
    <strong>First Name:</strong> <?= htmlspecialchars($member->getFirstName()) ?><br>
    <strong>Last Name:</strong> <?= htmlspecialchars($member->getLastName()) ?><br>
    <strong>Email:</strong> <?= htmlspecialchars($member->getEmail()) ?><br>
    <strong>Card Number:</strong> <?= htmlspecialchars($member->getCardNumber()) ?><br>
    <strong>Registered At:</strong> <?= htmlspecialchars($member->getRegisteredAt()) ?>
</p>

<h2>Current Loans</h2>
<?php
$currentLoans = array_filter($history ?? [], function($loan) {
    return !$loan->getReturnDate();
});
?>
<?php if (!empty($currentLoans)): ?>
    <table>
        <thead>
            <tr>
                <th>Book</th>
                <th>Loan Date</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($currentLoans as $loan): ?>
            <tr>
                <td>
                    <?php
                    $book = method_exists($loan, 'getBook') ? $loan->getBook() : null;
                    echo $book ? htmlspecialchars($book->getTitle()) : htmlspecialchars($loan->getBookId());
                    ?>
                </td>
                <td><?= htmlspecialchars($loan->getLoanDate()) ?></td>
                <td><?= htmlspecialchars($loan->getDueDate()) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No current loans.</p>
<?php endif; ?>

<h2>Loan History</h2>
<?php
$historyReturned = array_filter($history ?? [], function($loan) {
    return $loan->getReturnDate();
});
?>
<?php if (!empty($historyReturned)): ?>
    <table>
        <thead>
            <tr>
                <th>Book</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Fine</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historyReturned as $loan): ?>
            <tr>
                <td>
                    <?php
                    $book = method_exists($loan, 'getBook') ? $loan->getBook() : null;
                    echo $book ? htmlspecialchars($book->getTitle()) : htmlspecialchars($loan->getBookId());
                    ?>
                </td>
                <td><?= htmlspecialchars($loan->getLoanDate()) ?></td>
                <td><?= htmlspecialchars($loan->getDueDate()) ?></td>
                <td><?= htmlspecialchars($loan->getReturnDate()) ?></td>
                <td><?= htmlspecialchars($loan->getFineAmount()) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No loan history.</p>
<?php endif; ?>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<!-- Add admin-specific options here -->
<?php endif; ?>

<p>
    <a href="/members">Back to Members list</a>
</p>
