<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
            $builder
                // ...
                ->add('xmlFile', FileType::class, [
                    'label' => 'XML',
                    'mapped' => false,
                    'required' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'application/xml',
                                'text/xml',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF document',
                        ])
                    ],
                    'attr'=>[
                        'class'=>'pb-5 pt-5'
                    ]
                ])
                ->add('Enviar', SubmitType::class,[
                    'attr'=>[
                        'class'=>'button is-primary'
                    ]
                ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
