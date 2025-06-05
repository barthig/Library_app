<?php
// views/auth/forgot_password.php
?>
<link rel="stylesheet" href="/styles.css">
<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-4 text-center">Password reset</h2>
    <?php if (!empty($flash)): ?>
        <div class="mb-3"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <form action="/auth/forgot-password" method="post">
        <label>Email:<input type="email" name="email" required></label><br>
        <button type="submit">Send reset link</button>
    </form>
    <div class="mt-3 text-center">
        <a href="/login">Back to login</a>
    </div>
</div>
