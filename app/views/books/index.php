<?php

?>

<h1>Books</h1>

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

<?php if (isset($_SESSION['isLogged']) && $_SESSION['isLogged'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="/books/create">Add New Book</a>
<?php endif; ?>

<?php if (!empty($books)): ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Year</th>
                <th>Total</th>
                <th>Available</th>
                <?php if (isset($_SESSION['isLogged']) && $_SESSION['isLogged'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= htmlspecialchars($book->getTitle(), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($book->getAuthorName() ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($book->getIsbn(), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($book->getPublicationYear(), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($book->getTotalCopies(), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($book->getAvailableCopies(), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if (isset($_SESSION['isLogged']) && $_SESSION['isLogged'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <td>
                            <a href="/books/<?= $book->getId() ?>/edit">Edit</a>
                            <form action="/books/<?= $book->getId() ?>/delete" method="post" style="display:inline">
                                <button type="submit" onclick="return confirm('Delete this book?')">Delete</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-data">No books found.</p>
<?php endif; ?>