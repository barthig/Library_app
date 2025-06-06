<?php

?>

<?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
    <p>You do not have permission to access this page.</p>
    <?php exit; ?>
<?php endif; ?>

<h1>Return Loan #<?= htmlspecialchars($loan->getId(), ENT_QUOTES, 'UTF-8') ?></h1>

<p>
    <strong>Book ID:</strong> <?= htmlspecialchars($loan->getBookId(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>Loan Date:</strong> <?= htmlspecialchars($loan->getLoanDate(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>Due Date:</strong> <?= htmlspecialchars($loan->getDueDate(), ENT_QUOTES, 'UTF-8') ?><br>
</p>

<?php if (!empty($_SESSION['errors'])): ?>
    <div class="errors" style="color: red; margin-bottom: 1em;">
        <ul>
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
        <?php unset($_SESSION['errors']); ?>
    </div>
<?php endif; ?>

<form action="/loans/<?= urlencode($loan->getId()) ?>/return" method="post">
    <!-- Example CSRF field; remove if you don't use this mechanism -->
    <?php if (!empty($_SESSION['csrf_token'])): ?>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <label for="return_date">
        Return Date:
        <input
            type="date"
            id="return_date"
            name="return_date"
            required
            value="<?= date('Y-m-d') ?>">
    </label>
    <br><br>

    <button type="submit">Return</button>
</form>