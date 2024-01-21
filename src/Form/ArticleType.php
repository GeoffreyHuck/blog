<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        $builder->add('language', null, [
            'label' => 'Language',
            'choice_label' => 'name',
            'required' => true,
        ]);

        $builder->add('rawContent', TextareaType::class, [
            'label' => 'Raw content in asciidoc',
            'attr' => [
                'class' => 'asciidoctor-editor',
                'rows' => 15,
            ],
        ]);

        $builder->add('themes', EntityType::class, [
            'label' => 'Add in articles listed for the following themes',
            'class' => Theme::class,
            'multiple' => true,
            'expanded' => true,
        ]);
        $builder->add('inMainMenu', null, [
            'label' => 'Put the entry in the main menu',
        ]);
        $builder->add('mainMenuTitle', null, [
            'label' => 'Title in the main menu',
        ]);
        $builder->add('position', null, [
            'label' => 'Position in the main menu. Used to sort the entries along with the themes in the main menu',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
