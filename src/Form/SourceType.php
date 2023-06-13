<?php
namespace App\Form;

use App\Entity\Source;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('upload', FileType::class, [
                'label_attr' => ['class' => 'custom-file-label'],
                'label' => '(KML/KMZ file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypesMessage' => 'Please upload a valid KMZ/KML document',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Source::class,
            'attr' => ['class' => 'upload-form-item']
        ]);
    }
}