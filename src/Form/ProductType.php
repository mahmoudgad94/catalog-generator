<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Tag;
use App\Enum\CustomFieldType;
use App\Repository\CustomFieldDefinitionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function __construct(
        private CustomFieldDefinitionRepository $customFieldRepo,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'USD',
                'scale' => 2,
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ProductImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'required' => false,
                'label' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $product = $event->getData();
            $definitions = $this->customFieldRepo->findBy([], ['position' => 'ASC']);

            foreach ($definitions as $def) {
                $currentValue = null;
                if ($product && $product->getId()) {
                    $cfv = $product->getCustomFieldValue($def);
                    $currentValue = $cfv?->getValue();
                }

                $fieldOptions = [
                    'mapped' => false,
                    'required' => $def->isRequired(),
                    'label' => $def->getName(),
                    'data' => $currentValue,
                ];

                $fieldType = match ($def->getFieldType()) {
                    CustomFieldType::Text => TextType::class,
                    CustomFieldType::Number => NumberType::class,
                    CustomFieldType::Boolean => CheckboxType::class,
                    CustomFieldType::Date => DateType::class,
                    CustomFieldType::Select => ChoiceType::class,
                };

                if ($def->getFieldType() === CustomFieldType::Boolean) {
                    $fieldOptions['data'] = (bool) $currentValue;
                }

                if ($def->getFieldType() === CustomFieldType::Select && $def->getOptions()) {
                    $fieldOptions['choices'] = array_combine($def->getOptions(), $def->getOptions());
                    $fieldOptions['placeholder'] = '— Select —';
                }

                if ($def->getFieldType() === CustomFieldType::Date && $currentValue) {
                    try {
                        $fieldOptions['data'] = new \DateTime($currentValue);
                    } catch (\Exception) {
                        $fieldOptions['data'] = null;
                    }
                }

                $form->add('custom_' . $def->getSlug(), $fieldType, $fieldOptions);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }
}
