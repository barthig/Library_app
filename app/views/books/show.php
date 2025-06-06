<?php

?>

<h1>Book Details</h1>

<p>
    <strong>Title:</strong> <?= htmlspecialchars($book->getTitle(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>ISBN:</strong> <?= htmlspecialchars($book->getIsbn(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>Publication Year:</strong> <?= htmlspecialchars($book->getPublicationYear(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>Total Copies:</strong> <?= htmlspecialchars($book->getTotalCopies(), ENT_QUOTES, 'UTF-8') ?><br>
    <strong>Available Copies:</strong> <?= htmlspecialchars($book->getAvailableCopies(), ENT_QUOTES, 'UTF-8') ?><br>
</p>

<p>
    <a href="/books/<?= urlencode($book->getId()) ?>/edit">Edit</a> |
<form action="/books/<?= urlencode($book->getId()) ?>/delete" method="post" style="display:inline;">
    <button type="submit" onclick="return confirm('Are you sure you want to delete this book?');">Delete</button>
</form> |
<a href="/books">Back to list</a>
</p>