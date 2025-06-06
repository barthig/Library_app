<?php

namespace App\Controllers;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Models\Author;
use App\Controllers\BaseController;

class AuthorController extends BaseController
{
    protected AuthorRepositoryInterface $authorRepo;

    public function __construct(AuthorRepositoryInterface $authorRepo)
    {
        $this->authorRepo = $authorRepo;
    }

    /**
     * Displays a list of all authors.
     */
    public function index()
    {
        $this->checkAuth();

        $authors = $this->authorRepo->findAll();
        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/authors/index.php';
    }

    /**
     * Displays the form for adding a new author.
     */
    public function createForm()
    {
        $this->checkAuth('admin');

        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/authors/create.php';
    }

    /**
     * Saves the new author.
     */
    public function store()
    {
        $this->checkAuth('admin');

        $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $birthDate = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_SPECIAL_CHARS); // Format YYYY-MM-DD
        $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_SPECIAL_CHARS);

        $author = new Author([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $birthDate,
            'country' => $country
        ]);

        // Assume Author performs its own validation (e.g., name is not empty)
        $errors = [];
        if (empty($firstName) || empty($lastName)) {
            $errors[] = "First name and last name are required.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /authors/create');
            exit;
        }

        $this->authorRepo->save($author);
        header('Location: /authors');
    }

    /**
     * Displays the author edit form.
     */
    public function editForm($id)
    {
        $this->checkAuth('admin');

        $author = $this->authorRepo->findById($id);
        if (!$author) {
            header("HTTP/1.0 404 Not Found");
            echo "Author not found.";
            exit;
        }
        include __DIR__ . '/../components/header.php';
        include __DIR__ . '/../views/authors/edit.php';
    }

    /**
     * Updates the author data.
     */
    public function update($id)
    {
        $this->checkAuth('admin');

        $author = $this->authorRepo->findById($id);
        if (!$author) {
            header("HTTP/1.0 404 Not Found");
            echo "Author not found.";
            exit;
        }

        $author->setFirstName(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS));
        $author->setLastName(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS));
        $author->setBirthDate(filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_SPECIAL_CHARS));
        $author->setCountry(filter_input(INPUT_POST, 'country', FILTER_SANITIZE_SPECIAL_CHARS));

        $errors = [];
        if (empty($author->getFirstName()) || empty($author->getLastName())) {
            $errors[] = "First name and last name are required.";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /authors/{$id}/edit");
            exit;
        }

        $this->authorRepo->update($author);
        header('Location: /authors');
    }

    /**
     * Deletes an author.
     */
    public function delete($id)
    {
        $this->checkAuth('admin');

        $this->authorRepo->delete($id);
        header('Location: /authors');
    }
}
