<?php

namespace App\Form;

use App\Entity\CustomFieldDefinition;
use App\Enum\CustomFieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('slug', TextType::class, [
                'help' => 'Machine name, used in CSV headers (e.g., "color", "weight")',
            ])
            ->add('fieldType', EnumType::class, [
                'class' => CustomFieldType::class,
                'choice_label' => fn(CustomFieldType $type) => ucfirst($type->value),
            ])
            ->add('options', TextareaType::class, [
                'required' => false,
                'help' => 'For "select" type: enter one option per line',
                'mapped' => false,
            ])
            ->add('position', IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 0],
            ])
            ->add('required', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CustomFieldDefinition::class]);
    }
}
