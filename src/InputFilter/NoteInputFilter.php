<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\AllowList;
use Laminas\Filter\Digits as DigitsFilter;
use Laminas\Filter\PregReplace;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripNewlines;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Date;
use Laminas\Validator\Digits as DigitsValidator;
use Laminas\Validator\InArray;
use Laminas\Validator\NotEmpty;

/**
 * NoteInputFilter provides basic input filtering and validation for "note" objects, or
 * the information submitted via the manage note form when creating and updating notes in
 * the application's underlying data store.
 *
 * It's not meant to be too complex nor sophisticated, as the needs of the application aren't.
 */
class NoteInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add(
            [
                'name'              => 'id',
                'required'          => false,
                'continue_if_empty' => true,
                'allow_empty'       => true,
                'validators'        => [
                    [
                        'name' => DigitsValidator::class,
                    ],
                ],
                'filters'           => [
                    [
                        'name' => StripTags::class,
                    ],
                    [
                        'name' => StringTrim::class,
                    ],
                    [
                        'name' => DigitsFilter::class,
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'              => 'title',
                'allow_empty'       => false,
                'validators'        => [
                    [
                        'name' => NotEmpty::class,
                    ],
                ],
                'filters'           => [
                    [
                        'name' => StripTags::class,
                    ],
                    [
                        'name' => StripNewlines::class,
                    ],
                    [
                        'name' => StringTrim::class,
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'              => 'body',
                'allow_empty'       => false,
                'validators'        => [
                    [
                        'name' => NotEmpty::class,
                    ],
                ],
                'filters'           => [
                    [
                        'name' => StripTags::class,
                    ],
                    [
                        'name' => StringTrim::class,
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'              => 'created',
                'allow_empty'       => true,
                'validators'        => [
                    [
                        'name' => NotEmpty::class,
                    ],
                    [
                        'name' => Date::class,
                    ]
                ],
                'filters'           => [
                    [
                        'name' => StripTags::class,
                    ],
                    [
                        'name' => StripNewlines::class,
                    ],
                    [
                        'name' => StringTrim::class,
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'              => 'category',
                'allow_empty'       => false,
                'validators'        => [
                    [
                        'name' => NotEmpty::class,
                    ],
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => [
                                'education',
                                'politics',
                                'sport',
                            ],
                            'strict' => true,
                        ],
                    ],
                ],
                'filters'           => [
                    [
                        'name' => StripTags::class,
                    ],
                    [
                        'name' => StringTrim::class,
                    ],
                    [
                        'name' => AllowList::class,
                        'options' => [
                            'list' => [
                                'education',
                                'politics',
                                'sport',
                            ],
                            'strict' => true,
                        ],
                    ],
                ],
            ]
        );
    }
}
