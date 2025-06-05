<?php
// views/auth/register.php
?>
<link rel="stylesheet" href="/styles.css">
<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-4 text-center">Registration</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="/auth/register" method="post">
        <label>First Name:<input type="text" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required></label><br>
        <label>Last Name:<input type="text" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required></label><br>
        <label>Email:<input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required></label><br>
        <label>Username:<input type="text" name="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required></label><br>
        <label>Password:<input type="password" name="password" required></label><br>
        <label>Repeat Password:<input type="password" name="password_repeat" required></label><br>
        <button type="submit">Register</button>
    </form>
    <div class="mt-3 text-center">
        <a href="/login">Already have an account? Log in</a>
    </div>
</div>
