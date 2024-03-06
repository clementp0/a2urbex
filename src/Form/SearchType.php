<?php

namespace App\Form;

use App\Class\Search;
use App\Entity\Country;
use App\Entity\Category;
use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\CountryRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SearchType extends AbstractType
{
    public function __construct(LocationRepository $locationRepository, Security $security) {
        $this->security = $security;
        $this->locationRepository = $locationRepository;
    }
    
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
                'query_builder' => function(CountryRepository $repository) { 
                    return $repository->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('category', EntityType::class, [
                'label' => 'Category',
                'class' => Category::class,
                'multiple' => true,
                'expanded' => true
            ]);


        if($this->security->getUser()->hasRole('ROLE_ADMIN')) {
            $sources = [];
            
            foreach($this->locationRepository->findAllSource() as $item) {
                $value = $item[1];
                if($value !== null) $sources[$value] = $value;
            }
            $sources['Autres'] = '0';
    
            $builder
                ->add('source', ChoiceType::class, [
                    'label' => 'Source',
                    'choices' => $sources,
                    'expanded'  => true,
                    'multiple'  => true,
                ]);
        }

        $builder
            ->add('submit', SubmitType::class, [
                'label' => 'Filter',
                'attr' => [
                    'class' => 'pin-filter-btn'
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