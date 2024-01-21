<?php
namespace App\Form;

use App\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, [
            'label' => 'Name',
        ]);
        $builder->add('url', null, [
            'label' => 'Url',
        ]);
        $builder->add('language', null, [
            'label' => 'Language',
            'choice_label' => 'name',
            'required' => true,
        ]);
        $builder->add('position', null, [
            'label' => 'Position in menu (smaller number first)',
        ]);
        $builder->add('pageArticle', null, [
            'label' => 'Article displayed as the content of the theme page',
            'required' => false,
            'choice_label' => 'title',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Theme::class,
        ]);
    }
}
