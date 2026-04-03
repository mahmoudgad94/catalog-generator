<?php

namespace App\Form;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $category = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class)
            ->add('parent', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => '— Root (no parent) —',
                'query_builder' => function (CategoryRepository $repo) use ($category) {
                    $qb = $repo->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                    if ($category && $category->getId()) {
                        $qb->where('c.id != :id')
                           ->setParameter('id', $category->getId());
                    }
                    return $qb;
                },
            ])
            ->add('position', IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 0],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
