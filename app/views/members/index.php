<?php
// Path: app/views/members/index.php
include __DIR__ . '/../../components/header.php';
$this->checkAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member List</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
<h1>Members</h1>

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

<?php if (isset($_SESSION['isLogged']) && $_SESSION['isLogged'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="/members/create">Add New Member</a>
<?php endif; ?>

<?php if (!empty($members)): ?>
<table>
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Card Number</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($members as $member): ?>
        <tr>
            <td><?= htmlspecialchars($member->getFirstName()) ?></td>
            <td><?= htmlspecialchars($member->getLastName()) ?></td>
            <td><?= htmlspecialchars($member->getEmail()) ?></td>
            <td><?= htmlspecialchars($member->getCardNumber()) ?></td>
            <td><?= htmlspecialchars($member->getRegisteredAt()) ?></td>
            <td>
                <a href="/members/<?= $member->getId() ?>">Details</a>
                <!-- Restrict edit and delete options to admin users -->
                <?php if (isset($_SESSION['isLogged']) && $_SESSION['isLogged'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/members/<?= $member->getId() ?>/edit">Edit</a>
                    <form action="/members/<?= $member->getId() ?>/delete" method="post" style="display:inline">
                        <button type="submit" onclick="return confirm('Delete this member?')">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p class="no-data">No members found.</p>
<?php endif; ?>
</div>
</body>
</html>
