<?php

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>

<link rel="stylesheet" href="/styles.css">

<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-4 text-center">Login</h2>
    <form action="/login" method="post">
        <div class="form-group mb-3">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-control"
                placeholder="Enter your username"
                required
                value="<?php echo isset(
                            $old['username']
                        ) ? htmlspecialchars($old['username']) : ''; ?>">
        </div>

        <div class="form-group mb-3">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Enter your password"
                required>
        </div>

        <div class="form-check mb-3">
            <input
                type="checkbox"
                id="remember"
                name="remember"
                class="form-check-input"
                <?php echo isset($old['remember']) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Log in</button>
    </form>

    <div class="mt-3 text-center">
        <a href="/auth/forgot-password">Forgot password?</a><br>
        <span>Don't have an account? <a href="/auth/register">Register</a></span>
    </div>
</div>