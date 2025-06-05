<?php
declare(strict_types=1);

namespace App\Models;


/**
 * "Member" model – represents a row in the `members` table.
 */
class Member
{
    /** @var int|null */
    private ?int $id;

    /** @var string */
    private string $first_name;

    /** @var string */
    private string $last_name;

    /** @var string */
    private string $email;

    /** @var string */
    private string $card_number;
    /** @var string */
    private string $password_hash;
    /** @var string */
    private string $username;

    /** @var string */
    private string $role;

    /** @var string|null Stored as "YYYY-MM-DD HH:MM:SS" or null before the first save */
    private ?string $registered_at;

    /**
     * Constructor accepts explicit types or null for ID / registered_at.
     */
    public function __construct(
    ?int $id,
    string $first_name,
    string $last_name,
    string $email,
    string $card_number,
    string $username,
    string $password_hash,
    ?string $registered_at = null,
    string $role = 'user'
) {
    $this->id            = $id;
    $this->first_name    = $first_name;
    $this->last_name     = $last_name;
    $this->email         = $email;
    $this->card_number   = $card_number;
    $this->username      = $username;
    $this->password_hash = $password_hash;
    $this->registered_at = $registered_at;
    $this->role          = $role;
}


    /**
     * Creates a Member instance from a PDO associative array.
     */
    public static function fromArray(array $data): Member
{
    return new self(
        isset($data['id'])            ? (int)$data['id']            : null,
        $data['first_name']    ?? '',
        $data['last_name']     ?? '',
        $data['email']         ?? '',
        $data['card_number']   ?? '',
        $data['username']      ?? '',
        $data['password_hash'] ?? '',
        $data['registered_at'] ?? null,
        $data['role']          ?? 'user'
    );
}
public function getUsername(): string
{
    return $this->username;
}

public function getPasswordHash(): string
{
    return $this->password_hash;
}

public function getRole(): string {
    return $this->role;
}

    /** @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return string */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /** @return string */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /** @return string */
    public function getEmail(): string
    {
        return $this->email;
    }

    /** @return string */
    public function getCardNumber(): string
    {
        return $this->card_number;
    }

    /** @return string|null */
    public function getRegisteredAt(): ?string
    {
        return $this->registered_at;
    }

    /** @param string $first_name */
    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    /** @param string $last_name */
    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    /** @param string $email */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /** @param string $card_number */
    public function setCardNumber(string $card_number): void
    {
        $this->card_number = $card_number;
    }
       /**
     * Sets a new username.
     *
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;

    }

    /**
     * Sets the user role (admin/user).
     *
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Basic validation of model data.
     * Returns an array of error messages. If empty – validation passed.
     */
    public function validate(): array
    {
        $errors = [];

        if (trim($this->first_name) === '') {
            $errors[] = 'First name is required.';
        }

        if (trim($this->last_name) === '') {
            $errors[] = 'Last name is required.';
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }

        if (trim($this->card_number) === '') {
            $errors[] = 'Card number is required.';
        }

        return $errors;
    }
}
