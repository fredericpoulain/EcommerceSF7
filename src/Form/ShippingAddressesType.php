<?php

namespace App\Form;

use App\Entity\ShippingAddresses;
use App\Repository\ShippingAddressesRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingAddressesType extends AbstractType
{
    public function __construct(private readonly Security $security, private readonly ShippingAddressesRepository $addressRepository)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'data' => $this->security->getUser()->getFirstname(),
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'data' => $this->security->getUser()->getLastname(),
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
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telephone',
                'attr' => [
                    'placeholder' => 'ex : 0600000000'
                ],
                'required' => false
            ])->add('isMain', CheckboxType::class, [
                'label' => 'Enregistrer comme adresse préférée',
                'required' => false
            ]);



    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShippingAddresses::class,
        ]);
    }
}
