<?php
namespace App\Repositories;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use PDO;
use InvalidArgumentException;

class AuthorRepository extends Repository implements AuthorRepositoryInterface
{
    /**
     * @param int $id
     * @return Author|null
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM authors WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Author($data) : null;
    }

    /**
     * @return Author[]
     */
    public function findAll()
    {
        $stmt = $this->db->query('SELECT * FROM authors ORDER BY last_name, first_name');
        $authors = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $authors[] = new Author($row);
        }

        return $authors;
    }

    /**
     * @param mixed $entity
     * @return void
     */
    public function save($entity)
    {
        if (! $entity instanceof Author) {
            throw new InvalidArgumentException(
                'AuthorRepository::save expects an instance of App\Models\Author.'
            );
        }

        // If the object already has an ID, delegate to update
        if ($entity->getId() !== null) {
            return $this->update($entity);
        }

        $stmt = $this->db->prepare(
            'INSERT INTO authors (
                first_name,
                last_name,
                birth_date,
                country
            ) VALUES (
                :first_name,
                :last_name,
                :birth_date,
                :country
            )'
        );
        $stmt->execute([
            'first_name' => $entity->getFirstName(),
            'last_name'  => $entity->getLastName(),
            'birth_date' => $entity->getBirthDate(),
            'country'    => $entity->getCountry(),
        ]);

        if (method_exists($entity, 'setId')) {
            $entity->setId((int) $this->db->lastInsertId());
        }
    }

    /**
     * @param mixed $entity
     * @return void
     */
    public function update($entity)
    {
        if (! $entity instanceof Author) {
            throw new InvalidArgumentException(
                'AuthorRepository::update expects an instance of App\\Models\\Author.'
            );
        }

        if ($entity->getId() === null) {
            throw new InvalidArgumentException(
                'AuthorRepository::update cannot update an Author object without an ID set.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE authors
             SET
                 first_name = :first_name,
                 last_name  = :last_name,
                 birth_date = :birth_date,
                 country    = :country
             WHERE id = :id'
        );
        $stmt->execute([
            'id'         => $entity->getId(),
            'first_name' => $entity->getFirstName(),
            'last_name'  => $entity->getLastName(),
            'birth_date' => $entity->getBirthDate(),
            'country'    => $entity->getCountry(),
        ]);
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM authors WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function findByName(string $fullName) {
        [$firstName, $lastName] = explode(' ', $fullName, 2) + [1 => ''];
        $stmt = $this->db->prepare('SELECT * FROM authors WHERE first_name = :first_name AND last_name = :last_name');
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name'  => $lastName
        ]);
        $row = $stmt->fetch();
        if ($row) {
            return new \App\Models\Author($row);
        }
        return null;
    }

    
    /**
     * Creates a new author based on provided data.
     * @param array $data ['first_name' => ..., 'last_name' => ...]
     * @return Author
     */
    public function create(array $data) {
        // Example using PDO:
        $stmt = $this->db->prepare('INSERT INTO authors (first_name, last_name) VALUES (:first_name, :last_name)');
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name']
        ]);
        $id = $this->db->lastInsertId();
        return $this->findById($id);
    }
}
