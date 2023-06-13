<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Country;
use App\Entity\Category;
use App\Entity\User;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Image;



class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Abandonned Castle'
                ]
            ])
            ->add('previousImage', HiddenType::class, [
                'data' => $options['previousImage'],
                'required' => false,
                'attr' => ['class' => 'custom-file-image']
            ])
            ->add('image', FileType::class, [
                'row_attr' => ['class' => 'custom-file-image-preview'],
                'label' => 'Image',
                'label_attr' => ['class' => 'custom-file-label'],
                'required' => false,
                'attr' => [
                    'accept' => ".jpg, .jpeg, .png",
                    'class' => 'custom-dd'
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => true,
            ])
            ->add('comments', TextType::class, [
                'label' => 'Comment',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Police with dog..',

                ]
            ])
            ->add('lat', NumberType::class, [
                'label' => 'Latitude',
                'required' => true,
                'attr' => [
                    'min' => -90,
                    'max' => 90,
                    'class' => 'coord-input'
                ],
            ])
            ->add('lon', NumberType::class, [
                'label' => 'Longitude',
                'required' => true,
                'attr' => [
                    'min' => -90,
                    'max' => 90,
                    'class' => 'coord-input'
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['title'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
            'previousImage' => null,
            'title' => null,
        ]);

    }
}