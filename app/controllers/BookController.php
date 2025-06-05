<?php
namespace App\Controllers;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
require_once __DIR__ . '/../factories/BookFactory.php';
use App\Factories\BookFactory;
use App\Controllers\BaseController;

class BookController extends BaseController {
    protected BookRepositoryInterface $bookRepo;
    protected AuthorRepositoryInterface $authorRepo;

    public function __construct(BookRepositoryInterface $bookRepo, AuthorRepositoryInterface $authorRepo) {
        $this->bookRepo   = $bookRepo;
        $this->authorRepo = $authorRepo;
    }

    /**
     * Displays a list of all books.
     */
    public function index() {
        $this->checkAuth();

        $books = $this->bookRepo->findAll();

        // For each Book fetch the corresponding author and store their full name:
        foreach ($books as $book) {
            $author = $this->authorRepo->findById($book->getAuthorId());
            $book->setAuthorName(
                $author
                    ? $author->getFirstName() . ' ' . $author->getLastName()
                    : '—'
            );
        }

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/books/index.php';
    }

    /**
     * Shows the form for adding a new book.
     */
    public function createForm() {
        $this->checkAuth('admin');

        // Retrieve all authors and pass them to the view
        $authors = $this->authorRepo->findAll();
        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/books/create.php';
    }

    /**
     * Saves a new book to the database.
     */
    public function store() {
        $this->checkAuth('admin');

        $title       = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
        $isbn        = filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_SPECIAL_CHARS);
        $pubYear     = filter_input(INPUT_POST, 'publication_year', FILTER_SANITIZE_NUMBER_INT);
        $authorId    = filter_input(INPUT_POST, 'author_id', FILTER_VALIDATE_INT);
        $authorName  = '';
        $totalCopies = filter_input(INPUT_POST, 'total_copies', FILTER_SANITIZE_NUMBER_INT);

        // Get the author's first and last name based on ID
        if ($authorId) {
            $author = $this->authorRepo->findById($authorId);
            if ($author) {
                $authorName = $author->getFirstName() . ' ' . $author->getLastName();
            }
        }

        // Create the model from the data using the factory
        $book = BookFactory::create([
            'title'            => $title,
            'isbn'             => $isbn,
            'publication_year' => $pubYear,
            'author_id'        => $authorId,
            'authorName'       => $authorName,
            'total_copies'     => $totalCopies
        ]);

        $errors = $book->validate();
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /books/create');
            exit;
        }

        // Check if the author already exists by full name (e.g., "Jan Kowalski")
        $existingAuthor = $this->authorRepo->findByName($authorName);
        if ($existingAuthor) {
            $authorId = $existingAuthor->getId();
        } else {
            // If not, create a new author (first/last name parsing can be improved)
            [$firstName, $lastName] = explode(' ', $authorName, 2) + [1 => ''];
            $newAuthor = $this->authorRepo->create([
                'first_name' => $firstName,
                'last_name'  => $lastName
            ]);
            $authorId = $newAuthor->getId();
        }

        // Set author_id in the model before saving the book
        $book->setAuthorId($authorId);
        $this->bookRepo->save($book);

        header('Location: /books');
        exit;
    }

    /**
     * Shows details of a single book.
     */
    public function show(int $bookId) {
        $this->checkAuth();

        $book = $this->bookRepo->findById($bookId);
        if (!$book) {
            header("HTTP/1.0 404 Not Found");
            echo "Book not found.";
            exit;
        }

        $author = $this->authorRepo->findById($book->getAuthorId());
        $book->setAuthorName(
            $author
                ? $author->getFirstName() . ' ' . $author->getLastName()
                : '—'
        );

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/books/show.php';
        exit;
    }

    /**
     * Shows the edit form for an existing book.
     */
    public function editForm(int $id) {
        $this->checkAuth('admin');

        $book = $this->bookRepo->findById($id);
        if (!$book) {
            header("HTTP/1.0 404 Not Found");
            echo "Book not found.";
            exit;
        }

        // Populate authorName so the form shows the current author
        $author = $this->authorRepo->findById($book->getAuthorId());
        $book->setAuthorName(
            $author
                ? $author->getFirstName() . ' ' . $author->getLastName()
                : ''
        );

        // Retrieve all authors and pass them to the view
        $authors = $this->authorRepo->findAll();
        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/books/edit.php';
    }

    /**
     * Updates the book with the given ID.
     */
    public function update(int $id) {
        $this->checkAuth('admin');

        $title       = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
        $isbn        = filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_SPECIAL_CHARS);
        $pubYear     = filter_input(INPUT_POST, 'publication_year', FILTER_SANITIZE_NUMBER_INT);
        $authorId    = filter_input(INPUT_POST, 'author_id', FILTER_VALIDATE_INT);
        $authorName  = '';
        if ($authorId) {
            $author = $this->authorRepo->findById($authorId);
            if ($author) {
                $authorName = $author->getFirstName() . ' ' . $author->getLastName();
            }
        }
        $totalCopies = filter_input(INPUT_POST, 'total_copies', FILTER_SANITIZE_NUMBER_INT);

        $book = $this->bookRepo->findById($id);
        if (!$book) {
            header("HTTP/1.0 404 Not Found");
            echo "Book not found.";
            exit;
        }

        // Set new field values
        $book->setTitle($title);
        $book->setIsbn($isbn);
        $book->setPublicationYear($pubYear);
        $book->setAuthorName($authorName);
        $book->setAuthorId($authorId);

        // Handle changing the number of copies
        $diff = $totalCopies - $book->getTotalCopies();
        $book->setTotalCopies($totalCopies);
        $book->setAvailableCopies($book->getAvailableCopies() + $diff);

        $errors = $book->validate();
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /books/{$id}/edit");
            exit;
        }

        // Check if the author exists
        // (do not create a new author during edit, just assign the selected one)
        $existingAuthor = $this->authorRepo->findByName($authorName);
        if ($existingAuthor) {
            $authorId = $existingAuthor->getId();
        } else {
            // If not found, create a new author
            [$firstName, $lastName] = explode(' ', $authorName, 2) + [1 => ''];
            $newAuthor = $this->authorRepo->create([
                'first_name' => $firstName,
                'last_name'  => $lastName
            ]);
            $authorId = $newAuthor->getId();
        }

        // Set author_id before updating
        $book->setAuthorId($authorId);
        $this->bookRepo->update($book);

        header('Location: /books');
        exit;
    }

    /**
     * Deletes a book.
     */
    public function delete(int $id) {
        $this->checkAuth('admin');

        $this->bookRepo->delete($id);
        header('Location: /books');
        exit;
    }
}
