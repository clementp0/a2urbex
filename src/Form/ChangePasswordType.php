<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'disabled' => true,
                'label' => 'Email',
            ])
            ->add('firstname', TextType::class,[
                'disabled' => true,
                'label' => 'Firstname',
            ])
            ->add('lastname', TextType::class,[
                'disabled' => true,
                'label' => 'Name',
            ])
            ->add('old_password', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'SuperPassword123',
                ],
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Password do not match.',
                'label' => 'New Password',
                'required' => true,
                'first_options' => ['label' => 'New Password'],
                'second_options' => ['label' => 'Confirm your new password']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
