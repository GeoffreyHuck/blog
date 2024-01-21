<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Theme;
use App\Model\MediaUpload;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MediaUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, [
            'label' => 'The name of the media with the extension (a watermark will be added on images with a lowercase extension)',
            'attr' => [
                'placeholder' => 'cover.JPG for the cover image',
            ],
        ]);
        $builder->add('file', FileType::class, [
            'label' => 'Media file (pdf or image)',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MediaUpload::class,
        ]);
    }
}
