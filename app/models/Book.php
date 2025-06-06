<?php

namespace App\Models;

class Book
{
    private ?int $id;
    private string $title;
    private string $isbn;
    private ?int $publication_year;
    private ?int $author_id;
    private int $total_copies;
    private int $available_copies;

    /**
     * Field for displaying the author's full name in views.
     * Not mapped to the database – used only for presentation and validation.
     */
    private ?string $authorName = null;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null;
        $this->title = $data['title'] ?? '';
        $this->isbn = $data['isbn'] ?? '';
        $this->publication_year = isset($data['publication_year']) && is_numeric($data['publication_year'])
            ? (int)$data['publication_year']
            : null;
        $this->author_id = isset($data['author_id']) && is_numeric($data['author_id'])
            ? (int)$data['author_id']
            : null;
        $this->total_copies = isset($data['total_copies']) && is_numeric($data['total_copies'])
            ? (int)$data['total_copies']
            : 1;
        $this->available_copies = isset($data['available_copies']) && is_numeric($data['available_copies'])
            ? (int)$data['available_copies']
            : $this->total_copies;

        // If authorName was provided in the array, set it:
        if (isset($data['authorName']) && is_string($data['authorName'])) {
            $this->authorName = trim($data['authorName']);
        }
    }

    // Gettery
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getIsbn(): string
    {
        return $this->isbn;
    }
    public function getPublicationYear(): ?int
    {
        return $this->publication_year;
    }
    public function getAuthorId(): ?int
    {
        return $this->author_id;
    }
    public function getTotalCopies(): int
    {
        return $this->total_copies;
    }
    public function getAvailableCopies(): int
    {
        return $this->available_copies;
    }
    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    // Settery
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }
    public function setPublicationYear(?int $year): void
    {
        $this->publication_year = $year;
    }
    public function setAuthorId(?int $authorId): void
    {
        $this->author_id = $authorId;
    }
    public function setTotalCopies(int $total): void
    {
        $this->total_copies = $total;
        // If available_copies was higher than the new total, adjust it:
        if ($this->available_copies > $this->total_copies) {
            $this->available_copies = $this->total_copies;
        }
    }
    public function setAvailableCopies(int $available): void
    {
        $this->available_copies = $available;
    }
    public function setAuthorName(?string $authorName): void
    {
        $this->authorName = $authorName !== null ? trim($authorName) : null;
    }

    /**
     * Validates book data.
     * Checks whether authorName (instead of just author_id) is a non-empty string,
     * and validates the remaining required fields.
     * Returns an array of error messages (empty if everything is OK).
     */
    public function validate(): array
    {
        $errors = [];

        if (trim($this->title) === '') {
            $errors[] = "Title is required.";
        }

        if (trim($this->isbn) === '') {
            $errors[] = "ISBN is required.";
        }

        if (!is_int($this->publication_year) || $this->publication_year < 1) {
            $errors[] = "Publication year must be a valid positive integer.";
        }

        // Zamiast weryfikacji author_id, sprawdzamy authorName
        if (trim((string)$this->authorName) === '') {
            $errors[] = "Author name is required.";
        }

        if (!is_int($this->total_copies) || $this->total_copies < 1) {
            $errors[] = "Total copies must be at least 1.";
        }

        if (!is_int($this->available_copies) || $this->available_copies < 0) {
            $errors[] = "Available copies cannot be negative.";
        }

        if (
            is_int($this->available_copies) && is_int($this->total_copies)
            && $this->available_copies > $this->total_copies
        ) {
            $errors[] = "Available copies cannot exceed total copies.";
        }

        return $errors;
    }
}
