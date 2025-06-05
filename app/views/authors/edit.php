<?php
// Path: app/views/authors/edit.php
?>

<h1>Edit Author</h1>

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

<form action="/authors/<?= $author->getId() ?>/edit" method="post">
    <label>First Name:
        <input type="text" name="first_name" value="<?= htmlspecialchars($author->getFirstName()) ?>" required>
    </label><br>
    <label>Last Name:
        <input type="text" name="last_name" value="<?= htmlspecialchars($author->getLastName()) ?>" required>
    </label><br>
    <label>Birth Date:
        <input type="date" name="birth_date" value="<?= htmlspecialchars($author->getBirthDate()) ?>">
    </label><br>
    <label>Country:
        <input type="text" name="country" value="<?= htmlspecialchars($author->getCountry()) ?>">
    </label><br>
    <button type="submit">Update</button>
</form>
