<?php

?>

<h1>Loans</h1>

<?php if (!empty($_SESSION['errors'])): ?>
    <div class="errors">
        <ul>
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php unset($_SESSION['errors']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="/loans/create">Create New Loan</a>
<?php endif; ?>

<?php if (!empty($loans)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Member</th>
                <th>Book Title</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Fine (PLN)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loans as $loan): ?>
                <tr>
                    <!-- Loan ID -->
                    <td>
                        <?= htmlspecialchars((string) $loan->getId()) ?>
                    </td>

                    <!-- Member full name instead of Member ID -->
                    <td>
                        <?php
                        // Assume getMember() returns a Member object
                        $member = $loan->getMember();
                        $fullName = $member
                            ? $member->getFirstName() . ' ' . $member->getLastName()
                            : '—';
                        ?>
                        <?= htmlspecialchars($fullName) ?>
                    </td>

                    <!-- Book title instead of Book ID -->
                    <td>
                        <?php
                        // Assume getBook() returns a Book object
                        $book = $loan->getBook();
                        $title = $book
                            ? $book->getTitle()
                            : '—';
                        ?>
                        <?= htmlspecialchars($title) ?>
                    </td>

                    <!-- Loan date -->
                    <td>
                        <?= htmlspecialchars((string) $loan->getLoanDate()) ?>
                    </td>

                    <!-- Due date -->
                    <td>
                        <?= htmlspecialchars((string) $loan->getDueDate()) ?>
                    </td>

                    <!-- Actual return date -->
                    <td>
                        <?= $loan->getReturnDate()
                            ? htmlspecialchars($loan->getReturnDate())
                            : '-' ?>
                    </td>

                    <!-- Fine amount -->
                    <td>
                        <?= $loan->getFineAmount() !== null
                            ? number_format($loan->getFineAmount(), 2)
                            : '0.00' ?>
                    </td>

                    <!-- Actions: return / history -->
                    <td>
                        <?php if ($loan->getReturnDate() === null): ?>
                            <a href="/loans/<?= $loan->getId() ?>/return">Return</a>
                        <?php else: ?>
                            Returned
                        <?php endif; ?>
                        &nbsp;|&nbsp;
                        <a href="/loans/history/<?= htmlspecialchars($member ? $member->getId() : '') ?>">
                            History
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            &nbsp;|&nbsp;
                            <a href="/loans/<?= $loan->getId() ?>/edit">Edit</a>
                            <form action="/loans/<?= $loan->getId() ?>/delete" method="post" style="display:inline">
                                <button type="submit" onclick="return confirm('Delete this loan?')">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-data">No loans found.</p>
<?php endif; ?>