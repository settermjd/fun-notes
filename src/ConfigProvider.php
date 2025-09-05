<?php

namespace App;

use App\InputFilter\NoteInputFilter;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'input_filters' => $this->getInputFilters(),
        ];
    }

    public function getInputFilters() : array
    {
        return [
            'factories' => [
                NoteInputFilter::class => ReflectionBasedAbstractFactory::class,
            ],
        ];
    }
}
