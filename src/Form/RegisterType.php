<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname',TextType::class, [
                'attr' => [
                    'placeholder' => 'First Name'
                ]
            ])
            ->add('lastname',TextType::class, [
                'attr' => [
                    'placeholder' => 'Last Name'
                ]
            ])
            ->add('email',EmailType::class, [
                'attr' => [
                    'placeholder' => 'Email'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Passwords must be the same',
                'required' => true,
                'first_options' => ['label' => 'Password', 'attr' => [
                    'placeholder' => 'Password'
                ]],
                'second_options' => ['label' => 'Confirm Password','attr' => [
                    'placeholder' => 'Confirm Password'
                ]]
            ])
            ->add('submit',SubmitType::class, [
                'label' => 'Register',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
