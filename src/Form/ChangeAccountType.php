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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                'required' => false,
                'attr' => ['class' => 'custom-file-image']
            ])
            ->add('previousBanner', HiddenType::class, [
                'data' => $options['previousBanner'],
                'required' => false,
                'attr' => ['class' => 'custom-file-banner']
            ])
            ->add('image', FileType::class, [
                'row_attr' => ['class' => 'custom-file-image-preview'],
                'label' => 'Image',
                'label_attr' => ['class' => 'custom-file-label'],
                'required' => false,
                'attr' => [
                    'accept' => ".jpg, .jpeg, .png",
                ],
            ])
            ->add('banner', FileType::class, [
                'row_attr' => ['class' => 'custom-file-image-preview'],
                'label' => 'Banner',
                'label_attr' => ['class' => 'custom-file-label'],
                'required' => false,
                'attr' => [
                    'accept' => ".jpg, .jpeg, .png",
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
                'attr' => ['placeholder' => 'https://www.youtube.com/channel/UJR5GpH']
            ])
            ->add('tiktok', TextType::class,[
                'required' => false,
                'label' => 'TikTok',
                'attr' => ['placeholder' => 'a2urbex']
            ])
            ->add('instagram', TextType::class,[
                'required' => false,
                'label' => 'Instagram',
                'attr' => ['placeholder' => 'a2urbex']
            ])
            ->add('about', TextareaType::class,[
                'required' => false,
                'label' => 'About',
                'attr' => ['class' => 'long-text']
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
