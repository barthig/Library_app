<?php
namespace App\Models;

class Author {
    private ?int $id;
    private string $first_name;
    private string $last_name;
    private ?string $birth_date;
    private string $country;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->first_name = $data['first_name'] ?? '';
        $this->last_name = $data['last_name'] ?? '';
        $this->birth_date = $data['birth_date'] ?? null;
        $this->country = $data['country'] ?? '';
    }

    public function getId(): ?int { return $this->id; }
    public function getFirstName(): string { return $this->first_name; }
    public function getLastName(): string { return $this->last_name; }
    public function getBirthDate(): ?string { return $this->birth_date; }
    public function getCountry(): string { return $this->country; }
    public function setFirstName(string $first_name): void { $this->first_name = $first_name; }
    public function setLastName(string $last_name): void { $this->last_name = $last_name; }
    public function setBirthDate(?string $birth_date): void { $this->birth_date = $birth_date; }
    public function setCountry(string $country): void { $this->country = $country; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getFullName(): string {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * (Optional) validation of author data.
     */
    public function validate(): array {
        $errors = [];
        if (empty(trim($this->first_name))) {
            $errors[] = "First name is required.";
        }
        if (empty(trim($this->last_name))) {
            $errors[] = "Last name is required.";
        }
        return $errors;
    }
}
