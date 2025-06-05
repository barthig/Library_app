<?php
namespace App\Repositories;
use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use PDO;
use InvalidArgumentException;

class BookRepository extends Repository implements BookRepositoryInterface
{
    /**
     * @param int $id
     * @return Book|null
     */
    public function findById($id)
    {
        // Fetch book data with the assigned author_id (assuming at most one author)
        $stmt = $this->db->prepare(
            'SELECT 
                 b.id,
                 b.title,
                 b.isbn,
                 b.publication_year,
                 b.total_copies,
                 b.available_copies,
                 ba.author_id
             FROM books AS b
             LEFT JOIN book_author AS ba ON b.id = ba.book_id
             WHERE b.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Book($data) : null;
    }

    /**
     * @return Book[]
     */
    public function findAll()
    {
        // Fetch all books along with assigned author_id.
        // If a book has more than one author, one row per author will be returned.
        // We assume only a single author per book.
        $stmt = $this->db->query(
            'SELECT 
                 b.id,
                 b.title,
                 b.isbn,
                 b.publication_year,
                 b.total_copies,
                 b.available_copies,
                 ba.author_id
             FROM books AS b
             LEFT JOIN book_author AS ba ON b.id = ba.book_id
             ORDER BY b.title'
        );

        $books = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $books[] = new Book($row);
        }

        return $books;
    }

    /**
     * @param mixed $entity
     * @return void
     */
    public function save($entity)
    {
        if (! $entity instanceof Book) {
            throw new InvalidArgumentException(
                'BookRepository::save expects an instance of App\Models\Book.'
            );
        }

        // If the Book object already has an ID, call update instead of insert
        if ($entity->getId() !== null) {
            return $this->update($entity);
        }

        // 1) Insert the book
        $stmt = $this->db->prepare(
            'INSERT INTO books (
                title,
                isbn,
                publication_year,
                total_copies,
                available_copies
            ) VALUES (
                :title,
                :isbn,
                :publication_year,
                :total_copies,
                :available_copies
            )'
        );
        $stmt->execute([
            'title'            => $entity->getTitle(),
            'isbn'             => $entity->getIsbn(),
            'publication_year' => $entity->getPublicationYear(),
            'total_copies'     => $entity->getTotalCopies(),
            'available_copies' => $entity->getAvailableCopies(),
        ]);

        // 2) Set the ID in the model
        $newId = (int) $this->db->lastInsertId();
        if (method_exists($entity, 'setId')) {
            $entity->setId($newId);
        }

        // 3) If we have author_id, insert mapping into book_author
        $authorId = $entity->getAuthorId();
        if (is_int($authorId) && $authorId > 0) {
            $stmtMap = $this->db->prepare(
                'INSERT INTO book_author (book_id, author_id) VALUES (:book_id, :author_id)'
            );
            $stmtMap->execute([
                'book_id'   => $newId,
                'author_id' => $authorId
            ]);
        }
    }

    /**
     * @param mixed $entity
     * @return void
     */
    public function update($entity)
    {
        if (! $entity instanceof Book) {
            throw new InvalidArgumentException(
                'BookRepository::update expects an instance of App\Models\Book.'
            );
        }

        if ($entity->getId() === null) {
            throw new InvalidArgumentException(
                'BookRepository::update cannot update a Book object without an ID.'
            );
        }

        $bookId = $entity->getId();

        // 1) Update data in the books table
        $stmt = $this->db->prepare(
            'UPDATE books
             SET
                 title = :title,
                 isbn = :isbn,
                 publication_year = :publication_year,
                 total_copies = :total_copies,
                 available_copies = :available_copies
             WHERE id = :id'
        );
        $stmt->execute([
            'id'               => $bookId,
            'title'            => $entity->getTitle(),
            'isbn'             => $entity->getIsbn(),
            'publication_year' => $entity->getPublicationYear(),
            'total_copies'     => $entity->getTotalCopies(),
            'available_copies' => $entity->getAvailableCopies(),
        ]);

        // 2) Change mapping in book_author:
        //    a) First remove existing relation if present
        $stmtDel = $this->db->prepare(
            'DELETE FROM book_author WHERE book_id = :book_id'
        );
        $stmtDel->execute(['book_id' => $bookId]);

        //    b) Then insert a new relation if author_id is set
        $authorId = $entity->getAuthorId();
        if (is_int($authorId) && $authorId > 0) {
            $stmtMap = $this->db->prepare(
                'INSERT INTO book_author (book_id, author_id) VALUES (:book_id, :author_id)'
            );
            $stmtMap->execute([
                'book_id'   => $bookId,
                'author_id' => $authorId
            ]);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        // Thanks to ON DELETE CASCADE the relations in book_author will be removed automatically
        $stmt = $this->db->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
