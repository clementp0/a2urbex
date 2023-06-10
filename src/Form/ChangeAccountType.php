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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

class ChangeAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'disabled' => true,
                'label' => 'Email',
            ])
            ->add('previousImage', HiddenType::class, [
                'data' => $options['previousImage'],
                'required' => false
            ])
            ->add('previousBanner', HiddenType::class, [
                'data' => $options['previousBanner'],
                'required' => false
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'label_attr' => ['class' => 'image-label-placeholder'],
                'required' => false,
                'attr' => [
                    'accept' => ".jpg, .jpeg, .png",
                    'onchange'=>'previewImage(event)'
                ],
            ])
            ->add('banner', FileType::class, [
                'label' => 'Banner',
                'label_attr' => ['class' => 'banner-label-placeholder'],
                'required' => false,
                'attr' => [
                    'accept' => ".jpg, .jpeg, .png",
                    'onchange'=>'previewImage(event)'
                ],
            ])
            ->add('firstname', TextType::class,[
                'label' => 'Firstname',
            ])
            ->add('lastname', TextType::class,[
                'label' => 'Name',
            ])
            ->add('youtube', TextType::class,[
                'required' => false,
                'label' => 'YouTube',
            ])
            ->add('tiktok', TextType::class,[
                'required' => false,
                'label' => 'TikTok',
            ])
            ->add('instagram', TextType::class,[
                'required' => false,
                'label' => 'Instagram',
            ])
            ->add('about', TextType::class,[
                'required' => false,
                'label' => 'About',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'previousImage' => null,
            'previousBanner' => null,
            'title' => null,
        ]);

    }
}
