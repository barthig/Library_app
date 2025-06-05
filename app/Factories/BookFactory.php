<?php
// Path: app/factories/BookFactory.php
namespace App\Factories;

use App\Models\Book;

class BookFactory
{
    /**
     * Creates a Book object from the provided data.
     * Available array keys: title, isbn, publication_year,
     * author_id, authorName, total_copies.
     */
    public static function create(array $data): Book
    {
        $defaults = [
            'title'            => '',
            'isbn'             => '',
            'publication_year' => null,
            'author_id'        => null,
            'authorName'       => '',
            'total_copies'     => 1,
        ];
        $data = array_merge($defaults, $data);
        // Set available_copies to total_copies unless specified otherwise
        if (!isset($data['available_copies'])) {
            $data['available_copies'] = (int)$data['total_copies'];
        }

        return new Book($data);
    }
}
