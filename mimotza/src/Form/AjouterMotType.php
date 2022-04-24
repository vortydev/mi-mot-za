<?php

namespace App\Form;
use App\Entity\Langue;
use App\Entity\Mot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
class AjouterMotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        
       
        $builder
            ->add('mot', TextType::class,[
                'label' => ' mot :',
                'required'=> true
            ])
            ->add('dateAjout', HiddenType::class, [
                'empty_data' => new \DateTime('now'),
                'label' => ' ',
                'required' => False
            ])
            ->add('idLangue', EntityType::class, [
                'class' => Langue::class,
                'choice_label' => 'Langue',
                'label' => 'Langue : ',
                'multiple' => False,
                'required' => True
                
            ]
            )
            
            ->add('Ajouter', SubmitType::class,[
                'label' => 'Ajouter'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mot::class,
        ]);
    }
}
