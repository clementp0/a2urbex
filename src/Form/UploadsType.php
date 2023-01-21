<?php
namespace App\Form;

use App\Entity\Uploads;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uploads', FileType::class, [
                'label' => '(KML/KMZ file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        // 'mimeTypes' => [
                        //     'application/kmz',
                        //     'application/kml',
                        //     'application/mp3',
                        // ],
                        'mimeTypesMessage' => 'Please upload a valid KMZ/KML document',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Uploads::class,
            'attr' => ['class' => 'upload-form-item']
        ]);
    }
}