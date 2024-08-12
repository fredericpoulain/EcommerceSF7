<?php

namespace App\Form;

use App\Entity\BillingAddresses;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
class BillingAddressesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
// la vérif se fait dans le fichier entity. donc inutile ici, mais je laisse la syntaxe :
//                'constraints' => [
//                    new Assert\NotBlank(message: "Le prénom ne peut pas être vide"),
//                    new Assert\Length(
//                        max: 150,
//                        maxMessage: 'Prénom trop longue',
//                    ),
//                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse'
            ])
//            ->add('additional', TextType::class, [
//                'label' => 'Complément d\'adresse',
//                'required' => false
//            ])
            ->add('zipcode', TextType::class, [
                'label' => 'Code Postal'
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville'
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BillingAddresses::class,
        ]);
    }
}
