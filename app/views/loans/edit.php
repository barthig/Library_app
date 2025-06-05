<?php
// Path: app/views/loans/edit.php
?>
<h1>Edit Loan #<?= htmlspecialchars($loan->getId(), ENT_QUOTES, 'UTF-8') ?></h1>

<?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
<p>You do not have permission to edit loans.</p>
<?php exit; ?>
<?php endif; ?>

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

<p>
    <strong>Member ID:</strong> <?= htmlspecialchars((string)$loan->getMemberId(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>Book ID:</strong> <?= htmlspecialchars((string)$loan->getBookId(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>Loan Date:</strong> <?= htmlspecialchars($loan->getLoanDate(), ENT_QUOTES, 'UTF-8') ?><br>
</p>

<form action="/loans/<?= urlencode($loan->getId()) ?>/edit" method="post">
    <label>Due Date:
        <input type="date" name="due_date" value="<?= htmlspecialchars($loan->getDueDate(), ENT_QUOTES, 'UTF-8') ?>" required>
    </label><br>
    <button type="submit">Update</button>
</form>
<p><a href="/loans">Back to Loans list</a></p>

