<?php
// Path: app/views/books/create.php
?>
<div class="container">
    <nav style="margin-bottom: 24px;">
        <a href="/books">Books</a> |
        <a href="/authors">Authors</a> |
        <a href="/members">Members</a> |
        <a href="/loans">Loans</a>
    </nav>

    <h1>Add New Book</h1>

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

    <form action="/books" method="post">
        <label for="title">Title:
            <input type="text" id="title" name="title" required>
        </label><br>

        <label for="isbn">ISBN:
            <input type="text" id="isbn" name="isbn" required>
        </label><br>

        <label for="publication_year">Publication Year:
            <input type="number" id="publication_year" name="publication_year" required>
        </label><br>

        <label for="total_copies">Total Copies:
            <input type="number" id="total_copies" name="total_copies" value="1" min="1" required>
        </label><br>

        <!-- Select field for choosing author -->
        <label for="author_id">Author:
            <select id="author_id" name="author_id" required>
                <option value="">-- choose author --</option>
                <?php foreach ($authors as $author): ?>
                    <?php
                        $id    = $author->getId();
                        $fname = $author->getFirstName();
                        $lname = $author->getLastName();
                    ?>
                    <option value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars("$fname $lname", ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>

        <!-- Ensure admin-specific options are visible only to admin users -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <!-- Admin-specific options can be added here -->
        <?php endif; ?>

        <button type="submit">Save</button>
    </form>
    <p>
        <a href="/books">Back to book list</a>
    </p>
</div>
