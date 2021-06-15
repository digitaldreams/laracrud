<?php

namespace LaraCrud\Helpers;

use DbReader\Column;

class FakerColumn
{
    protected $map = [
        'firstName' => [
            'first_name',
            'first_name',
            'fname',
        ],
        'lastName' => [
            'last_name',
            'lastname',
            'lname',
        ],
        'name' => [
            'full_name',
            'name',
        ],
        'safeEmail' => [
            'email',
            'email_address',
            'emailaddress',
        ],
        'address' => [
            'address',
            'street_address',
            'street',
        ],
        'city' => [
            'city',
            'suburb',
            'locality',
            'state',
            'village',
            'town',
        ],
        'country' => [
            'country',
        ],
        'realText()' => [
            'title',
            'subject',
            'message',
            'reply',
            'comments',
            'comment',
            'feedback',
            'body',
            'content',
            'description',
            'about',
            'profile',
        ],
        'slug' => [
            'slug',
        ],
        'phoneNumber' => [
            'phone',
            'phone_number',
            'mobile',
            'cell',
            'mobile_number',
            'cell_number',
            'telephone',
            'personal_number',
            'business_number',
            'emergency_cell',
            'emergency_phone',
        ],
        'imageUrl()' => [
            'avatar',
            'photo',
            'image',
            'image_url',
            'document',
            'file',
        ],
    ];
    /**
     * @var Column
     */
    protected $column;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function get()
    {
    }

    public function default()
    {
        switch ($this->column->type()) {
            case 'varchar':
                foreach ($this->map as $faker => $columns) {
                    if (in_array($this->column->name(), $columns)) {
                        return '$this->faker->' . $faker;
                    }
                }

                return '$this->faker->words(5,true)';
                break;
            case 'enum':
                return 'array_rand([\'' . implode("','", $this->column->options()) . '\'], 1)';
                break;
            case 'longtext':
            case 'mediumtext':
            case 'text':
            case 'tinytext':
                if (in_array($this->column->name(), $this->map['realText()'])) {
                    return '$this->faker->realText()';
                } else {
                    return '$this->faker->text';
                }
                break;
            // Numeric data Type
            case 'bigint':
            case 'mediumint':
            case 'int':
                return '$this->faker->randomNumber()';
                break;
            case 'smallint':
            case 'tinyint':
                return '$this->faker->numberBetween(1,99)';
                break;
            case 'decimal':
            case 'float':
            case 'double':
                return '$this->faker->randomNumber()';
                break;
            // Date Time
            case 'date':
                return '$this->faker->date()';
                break;
            case 'datetime':
            case 'timestamp':
                return '$this->faker->dateTime()';
                break;
            case 'time':
                return '$this->faker->time()';
            case 'year':
                return '$this->faker->year';
                break;
        }
    }
}
