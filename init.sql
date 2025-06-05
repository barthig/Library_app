-- ================================================================
-- Script: init.sql
-- Description: Full script initializing the library database –
--              creates tables, indexes, functions, triggers, views and
--              inserts sample data (including assigning authors to books).
-- ================================================================

BEGIN;

-- ------------------------------------------------------------------------
-- 0. Remove old tables (if they exist) to start from a clean state
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS book_author CASCADE;
DROP TABLE IF EXISTS loans CASCADE;
DROP TABLE IF EXISTS members CASCADE;
DROP TABLE IF EXISTS books CASCADE;
DROP TABLE IF EXISTS authors CASCADE;

-- ------------------------------------------------------------------------
-- 1. Extensions
-- ------------------------------------------------------------------------
-- We use pgcrypto to hash passwords in the members table
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- ------------------------------------------------------------------------
-- 2. Table definitions
-- ------------------------------------------------------------------------

-- 2.1. Authors table
CREATE TABLE authors (
    id SERIAL PRIMARY KEY,            -- unique author ID
    first_name VARCHAR(100) NOT NULL, -- author first name
    last_name VARCHAR(100) NOT NULL,  -- author last name
    birth_date DATE,                  -- birth date (optional)
    country VARCHAR(100)              -- country of origin (optional)
);

-- 2.2. Books table
CREATE TABLE books (
    id SERIAL PRIMARY KEY,                          -- unique book ID
    title VARCHAR(255) NOT NULL,                    -- book title
    isbn VARCHAR(20) NOT NULL UNIQUE,               -- ISBN number
    publication_year INT NOT NULL CHECK (publication_year > 0), 
    total_copies INT    NOT NULL DEFAULT 1 CHECK (total_copies >= 0),
    available_copies INT NOT NULL DEFAULT 1 CHECK (available_copies >= 0 AND available_copies <= total_copies)
);

-- 2.3. Table linking books with authors (many-to-many)
CREATE TABLE book_author (
    book_id   INT NOT NULL,
    author_id INT NOT NULL,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id)   REFERENCES books(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 2.4. Library members table
CREATE TABLE members (
    id SERIAL PRIMARY KEY,                  -- unique member ID
    first_name VARCHAR(100)    NOT NULL,    -- first name
    last_name VARCHAR(100)     NOT NULL,    -- last name
    email VARCHAR(150)         NOT NULL UNIQUE, 
    username VARCHAR(50)       NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,    -- hashed password
    card_number VARCHAR(50)    NOT NULL UNIQUE, 
    registered_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    role VARCHAR(50)          NOT NULL DEFAULT 'user'  -- role (admin or user)
);

-- 2.5. Loans table
CREATE TABLE loans (
    id SERIAL PRIMARY KEY, 
    member_id INT NOT NULL, 
    book_id   INT NOT NULL, 
    loan_date DATE NOT NULL DEFAULT CURRENT_DATE,  -- loan date
    due_date  DATE NOT NULL,                       -- return deadline
    return_date DATE,                              -- return date (NULL = still borrowed)
    fine_amount NUMERIC(10,2) NOT NULL DEFAULT 0 CHECK (fine_amount >= 0),
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (book_id)   REFERENCES books(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_due_after_loan    CHECK (due_date > loan_date),
    CONSTRAINT chk_return_after_loan CHECK (return_date IS NULL OR return_date >= loan_date)
);

-- ------------------------------------------------------------------------
-- 3. Indexes for performance (optional because some UNIQUE constraints create indexes automatically)
-- ------------------------------------------------------------------------
CREATE INDEX idx_books_publication_year ON books(publication_year);
CREATE INDEX idx_loans_member_id      ON loans(member_id);
CREATE INDEX idx_loans_book_id        ON loans(book_id);

-- ------------------------------------------------------------------------
-- 4. Functions and triggers for updating available_copies in the books table
-- ------------------------------------------------------------------------

-- 4.1. Function decreasing available_copies after borrowing
CREATE OR REPLACE FUNCTION decrease_available_copies() RETURNS TRIGGER AS $$
DECLARE
    cur_available INT;
BEGIN
    SELECT available_copies
      INTO cur_available
    FROM books
    WHERE id = NEW.book_id;

    IF cur_available <= 0 THEN
        RAISE EXCEPTION 'No available copies of book id = %', NEW.book_id;
    END IF;

    UPDATE books
       SET available_copies = cur_available - 1
     WHERE id = NEW.book_id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 4.2. Function increasing available_copies after return
CREATE OR REPLACE FUNCTION increase_available_copies() RETURNS TRIGGER AS $$
BEGIN
    -- If during UPDATE the return_date field became NOT NULL (book returned),
    -- increase the number of available copies by 1.
    IF NEW.return_date IS NOT NULL AND OLD.return_date IS NULL THEN
        UPDATE books
           SET available_copies = available_copies + 1
         WHERE id = NEW.book_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 4.3. Trigger calling decrease_available_copies after inserting a new row into loans
CREATE TRIGGER trg_decrease_available
AFTER INSERT ON loans
FOR EACH ROW
WHEN (NEW.return_date IS NULL)
EXECUTE FUNCTION decrease_available_copies();

-- 4.4. Trigger calling increase_available_copies when return_date changes from NULL to a value
CREATE TRIGGER trg_increase_available
AFTER UPDATE OF return_date ON loans
FOR EACH ROW
WHEN (NEW.return_date IS NOT NULL AND OLD.return_date IS NULL)
EXECUTE FUNCTION increase_available_copies();

-- ------------------------------------------------------------------------
-- 6. Inserting sample data
-- ------------------------------------------------------------------------

-- 6.1. Insert authors
INSERT INTO authors (first_name, last_name, birth_date, country) VALUES
    ('J.K.',       'Rowling',      '1965-07-31',      'United Kingdom'),
    ('George R.R.', 'Martin',       '1948-09-20',      'United States'),
    ('Isaac',      'Asimov',        '1920-01-02',      'Russia'),
    ('Stephen',    'King',          '1947-09-21',      'United States'),
    ('Boleslaw',   'Prus',          '1847-08-20',      'Poland');

-- 6.2. Insert books
INSERT INTO books (title, isbn, publication_year, total_copies, available_copies) VALUES
    ('Harry Potter and the Sorcerer''s Stone', '9780439708180', 1997, 5, 5),
    ('A Game of Thrones',                     '9780553103540', 1996, 3, 3),
    ('Foundation',                            '9780553293357', 1951, 4, 4),
    ('The Shining',                           '9780385121675', 1977, 2, 2),
    ('The Doll',                              '9788308054078', 1890, 3, 3);

INSERT INTO book_author (book_id, author_id) VALUES
    (1, 1),  -- Harry Potter ← J.K. Rowling (author id 1)
    (2, 2),  -- A Game of Thrones ← George R.R. Martin (author id 2)
    (3, 3),  -- Foundation ← Isaac Asimov (author id 3)
    (4, 4),  -- The Shining ← Stephen King (author id 4)
    (5, 5);  -- The Doll ← Boleslaw Prus (author id 5)

-- 6.3. Insert library members
-- Passwords are hashed with the crypt(...) function using gen_salt('bf')
INSERT INTO members (first_name, last_name, email, username, password_hash, card_number, role) VALUES
    ('John',      'Doe',        'john.doe@example.com',       'johndoe',   crypt('Password123!', gen_salt('bf')),     'CARD001', 'admin'),
    ('Jane',      'Smith',      'jane.smith@example.com',     'janesmith', crypt('Qwerty456$',    gen_salt('bf')),     'CARD002', 'user'),
    ('Adam',      'Kowalski',   'adam.kowalski@library.pl','adamk',     crypt('Zxcvbn789#',    gen_salt('bf')),     'CARD003', 'user'),
    ('Ewa',       'Nowak',      'ewa.nowak@library.pl',    'ewan',      crypt('SecurePassword!', gen_salt('bf')),     'CARD004', 'user'),
    ('Admin',     'User',       'admin@example.com',          'admin',     crypt('admin', gen_salt('bf')),     'ADMIN001', 'admin'),
    ('Regular',   'User',       'user@example.com',           'user',      crypt('user', gen_salt('bf')),     'USER001', 'user');

-- 6.4. Loans
-- 6.4.1. Active loan (John borrows "Harry Potter")
INSERT INTO loans (member_id, book_id, loan_date, due_date, return_date, fine_amount) VALUES
    (1, 1, '2025-06-01', '2025-06-15', NULL, 0);

-- 6.4.2. Quick return (Jane borrows and quickly returns "Foundation")
INSERT INTO loans (member_id, book_id, loan_date, due_date, return_date, fine_amount) VALUES
    (2, 3, '2025-05-20', '2025-06-03', '2025-06-02', 0);

-- 6.4.3. Late return (Adam and "The Shining")
INSERT INTO loans (member_id, book_id, loan_date, due_date, return_date, fine_amount) VALUES
    (3, 4, '2025-05-10', '2025-05-20', '2025-05-25', 5.00);

-- 6.4.4. New active loan (Ewa borrows "The Doll")
INSERT INTO loans (member_id, book_id, loan_date, due_date, return_date, fine_amount) VALUES
    (4, 5, '2025-06-02', '2025-06-16', NULL, 0);

COMMIT;
