<link rel="stylesheet" href="/styles.css">
<header>
    <nav>
        <ul>
            <li><a href="/books">Books</a></li>
            <li><a href="/members">Members</a></li>
            <li><a href="/loans">Loans</a></li>
            <li><a href="/authors">Authors</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="/admin">Admin</a></li>
            <?php endif; ?>
            <li>
                <form action="/logout" method="POST" style="display:inline;">
                    <button type="submit">Logout</button>
                </form>
            </li>
        </ul>
    </nav>
</header>
