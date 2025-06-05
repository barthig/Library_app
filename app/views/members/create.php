<?php
// Path: app/views/members/create.php
$this->checkAuth('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Member</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
<h1>Add New Member</h1>

<?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
<p>You do not have permission to add new members.</p>
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

<form action="/members" method="post">
    <label>First Name:
        <input type="text" name="first_name" required>
    </label><br>
    <label>Last Name:
        <input type="text" name="last_name" required>
    </label><br>
    <label>Email:
        <input type="email" name="email" required>
    </label><br>
    <label>Username:
        <input type="text" name="username" required>
    </label><br>
    <label>Password:
        <input type="password" name="password" required>
    </label><br>
    <label>Repeat Password:
        <input type="password" name="password_repeat" required>
    </label><br>
    <button type="submit">Save</button>
</form>
</div>
</body>
</html>
