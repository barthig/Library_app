<?php
// Path: app/views/books/edit.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
    <nav style="margin-bottom: 24px;">
        <a href="/books">Books</a> |
        <a href="/authors">Authors</a> |
        <a href="/members">Members</a> |
        <a href="/loans">Loans</a>
    </nav>

    <h1>Edit Book</h1>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="errors">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <form action="/books/<?= $book->getId() ?>/edit" method="post">
        <label>Title:
            <input
                type="text"
                name="title"
                value="<?= htmlspecialchars($book->getTitle(), ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </label><br>

        <label>ISBN:
            <input
                type="text"
                name="isbn"
                value="<?= htmlspecialchars($book->getIsbn(), ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </label><br>

        <label>Publication Year:
            <input
                type="number"
                name="publication_year"
                value="<?= htmlspecialchars((string)$book->getPublicationYear(), ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </label><br>

        <!-- Restored author list display -->
        <label>Author:
            <select name="author_id" required>
                <option value="">-- choose author --</option>
                <?php foreach ($authors as $author): ?>
                    <?php
                        $authorId   = $author->getId();
                        $firstName  = $author->getFirstName();
                        $lastName   = $author->getLastName();
                        $selected   = $authorId === $book->getAuthorId() ? 'selected' : '';
                    ?>
                    <option
                        value="<?= htmlspecialchars((string)$authorId, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $selected ?>
                    >
                        <?= htmlspecialchars($firstName . ' ' . $lastName, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>

        <label>Total Copies:
            <input
                type="number"
                name="total_copies"
                value="<?= htmlspecialchars((string)$book->getTotalCopies(), ENT_QUOTES, 'UTF-8') ?>"
                min="1"
                required
            >
        </label><br>

        <button type="submit">Update</button>
    </form>
    <p>
        <a href="/books">Back to Book list</a>
    </p>
</div>
</body>
</html>