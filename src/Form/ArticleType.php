<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('publishedAt', null, [
            'label' => 'Published At (nothing is Unpublished)',
            'attr' => [
                'class' => 'date-field'
            ]
        ]);
        $builder->add('title', null, [
            'label' => 'Title',
        ]);
        $builder->add('url', null, [
            'label' => 'Url',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
