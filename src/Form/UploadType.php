<?php
namespace App\Form;

use App\Entity\Upload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('upload', FileType::class, [
                'label' => '(KML/KMZ file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        // 'mimeTypes' => [
                        //     'application/kmz',
                        //     'application/kml',
                        // ],
                        'mimeTypesMessage' => 'Please upload a valid KMZ/KML document',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Upload::class,
            'attr' => ['class' => 'upload-form-item']
        ]);
    }
}