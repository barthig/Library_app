<?php
// Path: app/views/loans/create.php
?>

<h1>Create Loan</h1>

<?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
    <p>You do not have permission to create a new loan.</p>
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

<form action="/loans" method="post">
    <label>Member:
        <select name="member_id" required>
            <option value="">-- choose member --</option>
            <?php foreach ($members as $m): ?>
                <option value="<?= $m->getId() ?>">
                    <?= htmlspecialchars($m->getFirstName() . ' ' . $m->getLastName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Book:
        <select name="book_id" required>
            <option value="">-- choose book --</option>
            <?php foreach ($books as $b): ?>
                <option value="<?= $b->getId() ?>">
                    <?= htmlspecialchars($b->getTitle()) ?> (Available: <?= $b->getAvailableCopies() ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <label>Due Date:
        <input
            type="date"
            name="due_date"
            required
            value="<?= date('Y-m-d', strtotime('+14 days')) ?>"
        >
    </label><br>

    <button type="submit">Create</button>
</form>
<p>
        <a href="/loans">Back to loan list</a>
    </p>

