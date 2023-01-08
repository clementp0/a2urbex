<?php

namespace App\Form;

use App\Class\Search;
use App\Entity\Country;
use App\Entity\Type;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{

    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = array('csrf_protection' => false);
        $builder
            ->add('string', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Abandonned Rocket...',
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('country', EntityType::class, [
                'label' => 'Country',
                'required' => false,
                'class' => Country::class,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filter',
                'attr' => [
                    'class' => 'btn-block btn-info'
                ]
            ])
            ->setMethod('GET')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Search::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false
            
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }

}