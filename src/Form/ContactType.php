<?php
namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Your name',
                ],
            ])
            ->add('company', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Your company',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Your email',
                ],
            ])
            ->add('message', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Your message',
                    'rows' => 8,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
