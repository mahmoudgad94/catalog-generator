<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class CsvImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('csvFile', FileType::class, [
            'label' => 'CSV File',
            'constraints' => [
                new File([
                    'maxSize' => '10M',
                    'mimeTypes' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
                    'mimeTypesMessage' => 'Please upload a valid CSV file.',
                ]),
            ],
        ]);
    }
}
