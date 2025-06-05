<?php
// Path: app/views/members/edit.php
$this->checkAuth('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Member</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
<h1>Edit Member</h1>

<?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
<p>You do not have permission to edit members.</p>
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

<form action="/members/<?= htmlspecialchars($member->getId(), ENT_QUOTES, 'UTF-8') ?>/edit" method="post">
    <label>First Name:
        <input type="text" name="first_name" value="<?= htmlspecialchars($member->getFirstName()) ?>" required>
    </label><br>
    <label>Last Name:
        <input type="text" name="last_name" value="<?= htmlspecialchars($member->getLastName()) ?>" required>
    </label><br>
    <label>Email:
        <input type="email" name="email" value="<?= htmlspecialchars($member->getEmail()) ?>" required>
    </label><br>
    <p><strong>Card Number:</strong> <?= htmlspecialchars($member->getCardNumber()) ?></p>
    <label>Role:
        <select name="role">
            <option value="user" <?= $member->getRole() === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $member->getRole() === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </label><br>
    <button type="submit">Update</button>
</form>
</div>
</body>
</html>
