<?php

?>

<h1>Add New Author</h1>

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

<form action="/authors" method="post">
    <label>First Name:
        <input type="text" name="first_name" required>
    </label><br>
    <label>Last Name:
        <input type="text" name="last_name" required>
    </label><br>
    <label>Birth Date:
        <input type="date" name="birth_date">
    </label><br>
    <label>Country:
        <input type="text" name="country">
    </label><br>
    <button type="submit">Save</button>
</form>
<p>
    <a href="/authors">Back to authors list</a>
</p>