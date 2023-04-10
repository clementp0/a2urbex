<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Country;
use App\Entity\Type;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Entity\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class NewLocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Abandonned Castel'
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPG, JPEG, PNG file)',
                'required' => false,
            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'required' => true,
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
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
            ->add('lat', TextType::class, [
                'label' => 'Latitude',
                'required' => true,
                'attr' => [
                    'placeholder' => '43.232166666667'
                ]
            ])
            ->add('lon', TextType::class, [
                'label' => 'Longitude',
                'required' => true,
                'attr' => [
                    'placeholder' => '-43.232166666667'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create Location',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
