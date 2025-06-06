<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Interfaces\MemberRepositoryInterface;
use App\Models\Member;
use PDO;
use InvalidArgumentException;


/**
 * Repository handling CRUD operations for the `members` table.
 */
class MemberRepository extends Repository implements MemberRepositoryInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generates the next sequential library card number.
     */
    public function getNextCardNumber(): string
    {
        $stmt = $this->db->query('SELECT MAX(id) FROM members');
        $maxId = (int)$stmt->fetchColumn();
        $next  = $maxId + 1;

        return sprintf('CARD%03d', $next);
    }

    /**
     * Retrieves all members (without pagination).
     *
     * @return Member[]
     */
    public function findAll()
    {
        $sql  = '
            SELECT
                id,
                first_name,
                last_name,
                email,
                card_number,
                username,
                password_hash,
                registered_at,
                role
            FROM members
            ORDER BY last_name, first_name
        ';

        $stmt = $this->db->query($sql);


        $members = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $members[] = Member::fromArray($row);
        }

        return $members;
    }

    /**
     * Finds a member by ID.
     *
     * @param int $id
     * @return Member|null
     */
    public function findById($id)
    {
        $sql  = '
            SELECT
                id,
                first_name,
                last_name,
                email,
                card_number,
                username,
                password_hash,
                registered_at,
                role
            FROM members
            WHERE id = :id
            LIMIT 1
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Member::fromArray($row);
    }

    /**
     * Finds a member by username.
     *
     * @param string $username
     * @return Member|null
     */
    public function findByUsername($username)
    {
        $sql  = '
            SELECT
                id,
                first_name,
                last_name,
                email,
                card_number,
                username,
                password_hash,
                registered_at,
                role
            FROM members
            WHERE username = :username
            LIMIT 1
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Member::fromArray($row);
    }

    /**
     * Saves a new member in the database.
     *
     * @param mixed $member
     * @return Member Returns the object with populated ID and registered_at.
     */
    public function save($member)
    {
        if (! $member instanceof Member) {
            throw new InvalidArgumentException(
                'MemberRepository::save expects an instance of App\\Models\\Member.'
            );
        }
        // generate card number automatically
        $cardNumber = $this->getNextCardNumber();
        $member->setCardNumber($cardNumber);
        $sql = '
            INSERT INTO members
                (first_name, last_name, email, card_number, username, password_hash, registered_at)
            VALUES
                (:first_name, :last_name, :email, :card_number, :username, :password_hash, NOW())
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'first_name'    => $member->getFirstName(),
            'last_name'     => $member->getLastName(),
            'email'         => $member->getEmail(),
            'card_number'   => $cardNumber,
            'username'      => $member->getUsername(),
            'password_hash' => $member->getPasswordHash(),
        ]);

        // Get the inserted row ID
        $newId = (int)$this->db->lastInsertId();

        // Reload to obtain the exact registered_at
        $saved = $this->findById($newId);
        if ($saved !== null) {
            return $saved;
        }

        // In the unlikely case findById returns null:
        $reflection = new \ReflectionClass($member);
        $propId     = $reflection->getProperty('id');
        $propId->setAccessible(true);
        $propId->setValue($member, $newId);

        return $member;
    }

    /**
     * Updates an existing member (by ID).
     *
     * @param mixed $member
     * @return bool Returns true on successful update.
     */
    public function update($member)
    {
        if (! $member instanceof Member) {
            throw new InvalidArgumentException(
                'MemberRepository::update expects an instance of App\\Models\\Member.'
            );
        }
        if ($member->getId() === null) {
            return false;
        }

        $sql = '
            UPDATE members
            SET
                first_name   = :first_name,
                last_name    = :last_name,
                email        = :email,
                card_number  = :card_number,
                username     = :username,
                role         = :role
            WHERE id = :id
        ';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'first_name'  => $member->getFirstName(),
            'last_name'   => $member->getLastName(),
            'email'       => $member->getEmail(),
            'card_number' => $member->getCardNumber(),
            'username'    => $member->getUsername(),
            'role'        => $member->getRole(),
            'id'          => $member->getId(),
        ]);
    }

    /**
     * Physically deletes a row from the `members` table.
     * (If you prefer a soft-delete, change to UPDATE is_active = 0.)
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql  = 'DELETE FROM members WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Checks whether a record with the given email already exists (unique constraint).
     *
     * @param string $email
     * @return bool
     */
    public function existsByEmail($email)
    {
        $sql  = 'SELECT 1 FROM members WHERE email = :email LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetchColumn();
    }


    /**
     * Checks whether a record with the given username already exists (unique constraint).
     *
     * @param string $username
     * @return bool
     */
    public function existsByUsername($username)
    {
        $sql  = 'SELECT 1 FROM members WHERE username = :username LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return (bool)$stmt->fetchColumn();
    }
}
