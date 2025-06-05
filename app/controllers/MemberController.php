<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\BaseController;


use App\Models\Member;
use App\Repositories\Interfaces\MemberRepositoryInterface;
use App\Repositories\Interfaces\LoanRepositoryInterface;

class MemberController extends BaseController
{
    private MemberRepositoryInterface $memberRepo;
    private LoanRepositoryInterface $loanRepo;

    public function __construct(MemberRepositoryInterface $memberRepo, LoanRepositoryInterface $loanRepo)
    {
        $this->memberRepo = $memberRepo;
        $this->loanRepo   = $loanRepo;
    }

    /**
     * Displays a list of all members.
     */
    public function index(): void
    {
        $this->checkAuth();
        
        $members = $this->memberRepo->findAll();

        // If there were flash messages in the session, pass them to the view:
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        include __DIR__ . '/../views/members/index.php';
    }

    /**
     * Shows the form for adding a new member.
     */
    public function createForm(): void
    {
        $this->checkAuth('admin');

        $this->startSessionIfNeeded();
        $errors   = $_SESSION['errors']   ?? [];
        $oldInput = $_SESSION['old_input'] ?? []; // so the form can be prefilled
        unset($_SESSION['errors'], $_SESSION['old_input']);

        include __DIR__ . '/../views/members/create.php';
    }

    /**
     * Stores a new member (POST method).
     */
    public function store(): void
    {
        $this->checkAuth('admin');

        $this->startSessionIfNeeded();

        // 1) Retrieve and initially validate data from the form (POST)
        $firstName  = trim((string) ($_POST['first_name']  ?? ''));
        $lastName   = trim((string) ($_POST['last_name']   ?? ''));
        $email      = trim((string) ($_POST['email']       ?? ''));
        $username   = trim((string) ($_POST['username']    ?? ''));
        $plainPass  = $_POST['password']                  ?? '';
        $plainPassRepeat = $_POST['password_repeat']      ?? '';

        // Keep old values so the form can be prefilled if there's an error
        $_SESSION['old_input'] = [
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $email,
            'username'    => $username
        ];

        // 2) Preliminary "dry" validation (just fields, no database checks)
        $errors = [];

        if ($firstName === '') {
            $errors[] = 'First name is required.';
        }
        if ($lastName === '') {
            $errors[] = 'Last name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if ($username === '') {
            $errors[] = 'Username is required.';
        }
        if ($plainPass === '') {
            $errors[] = 'Password is required.';
        }
        if ($plainPass !== $plainPassRepeat) {
            $errors[] = 'Passwords do not match.';
        }

        // 3) Check uniqueness in the database (email and username)
        if ($this->memberRepo->existsByEmail($email)) {
            $errors[] = 'Email is already taken.';
        }
        if ($this->memberRepo->existsByUsername($username)) {
            $errors[] = 'Username is already taken.';
        }

        // 4) If there are errors – store them in the session and return to the form
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /members/create');
            exit;
        }

        // 5) If everything is OK, hash the password and create the Member object
        $passwordHash = password_hash($plainPass, PASSWORD_DEFAULT);

        $generatedCard = $this->memberRepo->getNextCardNumber();
        $newMember = new Member(
            null,
            $firstName,
            $lastName,
            $email,
            $generatedCard,
            $username,
            $passwordHash,
            null
        );

        // 6) Zapis do bazy
        $saved = $this->memberRepo->save($newMember);

        // 7) Success message and redirect to the members list
        $_SESSION['flash'] = 'Member successfully added.';
        header('Location: /members');
        exit;
    }

    /**
     * Displays details of a single member.
     *
     * @param int $id
     */
    public function show(int $id): void
    {
        $this->checkAuth();

        $this->startSessionIfNeeded();

        $member = $this->memberRepo->findById($id);
        if ($member === null) {
            http_response_code(404);
            echo 'Member not found.';
            exit;
        }

        // Retrieve the loan history
        $history = $this->loanRepo->findByMember($id);

        include __DIR__ . '/../views/members/show.php';
    }

    /**
     * Shows the edit form for an existing member.
     *
     * @param int $id
     */
    public function editForm(int $id): void
    {
        $this->checkAuth('admin');
        $this->startSessionIfNeeded();

        $member = $this->memberRepo->findById($id);
        if ($member === null) {
            http_response_code(404);
            echo 'Member not found.';
            exit;
        }

        // Load any errors and old data for prefilling
        $errors   = $_SESSION['errors']   ?? [];
        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        include __DIR__ . '/../views/members/edit.php';
    }

    /**
     * Updates member data (POST method).
     *
     * @param int $id
     */
    public function update(int $id): void
    {
        $this->checkAuth('admin');
        $this->startSessionIfNeeded();

        $member = $this->memberRepo->findById($id);
        if ($member === null) {
            http_response_code(404);
            echo 'Member not found.';
            exit;
        }

        // 1) Retrieving new data from the form
        $firstName  = trim((string) ($_POST['first_name']  ?? ''));
        $lastName   = trim((string) ($_POST['last_name']   ?? ''));
        $email      = trim((string) ($_POST['email']       ?? ''));
        $username   = trim((string) ($_POST['username']    ?? ''));
        $role       = trim((string) ($_POST['role']        ?? 'user'));
        // Store values for prefilling in case of error:
        $_SESSION['old_input'] = [
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $email,
            'username'    => $username,
            'role'       => $role,
        ];

        // 2) Overwrite data in the Member object
        $member->setFirstName($firstName);
        $member->setLastName($lastName);
        $member->setEmail($email);
        $member->setUsername($username);
        $member->setRole($role);
        // Do not overwrite password_hash (changing the password may be a separate action)

        // 3) Basic validation
        $errors = $member->validate();
        if (!in_array($role, ['admin', 'user'], true)) {
            $errors[] = 'Invalid role.';
        }

        // 4) Check email and username uniqueness (only if they changed from the original)
        $original = $this->memberRepo->findById($id);
        if ($original !== null) {
            if ($original->getEmail() !== $email && $this->memberRepo->existsByEmail($email)) {
                $errors[] = 'Email is already taken.';
            }
            if ($original->getUsername() !== $username && $this->memberRepo->existsByUsername($username)) {
                $errors[] = 'Username is already taken.';
            }
        }

        // 5) If there are errors – save them and redirect back to the edit form
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /members/{$id}/edit");
            exit;
        }

        // 6) Save changes to the database
        $this->memberRepo->update($member);

        // 7) Feedback message and redirect to the list
        $_SESSION['flash'] = 'Member successfully updated.';
        header('Location: /members');
        exit;
    }

    /**
     * Physically deletes a member by ID.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->checkAuth('admin');
        $this->startSessionIfNeeded();

        $this->memberRepo->delete($id);

        $_SESSION['flash'] = 'Member successfully deleted.';
        header('Location: /members');
        exit;
    }

}
