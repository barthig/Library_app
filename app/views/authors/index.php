<?php
// Path: app/views/authors/index.php
global $_SESSION;
?>

<h1>Authors</h1>

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

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="/authors/create">Add New Author</a>
<?php endif; ?>


<?php if (!empty($authors)): ?>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Birth Date</th>
                <th>Country</th>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($authors as $author): ?>
                <tr>
                    <td><?= htmlspecialchars($author->getFullName()) ?></td>
                    <td><?= htmlspecialchars($author->getBirthDate()) ?></td>
                    <td><?= htmlspecialchars($author->getCountry()) ?></td>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <td>
                            <a href="/authors/<?= $author->getId() ?>/edit">Edit</a>
                            <form action="/authors/<?= $author->getId() ?>/delete" method="post" style="display:inline">
                                <button type="submit" onclick="return confirm('Delete this author?')">Delete</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-data">No authors found.</p>
<?php endif; ?>