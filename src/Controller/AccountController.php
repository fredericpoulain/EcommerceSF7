<?php

namespace App\Controller;

use App\Entity\BillingAddresses;
use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\ShippingAddresses;
use App\Form\BillingAddressesType;
use App\Form\InfosUserType;
use App\Form\ModifyPasswordType;
use App\Form\ShippingAddressesType;
use App\Repository\BillingAddressesRepository;
use App\Repository\OrdersDetailsRepository;
use App\Repository\ShippingAddressesRepository;
use App\Repository\UsersRepository;
use App\Security\EmailVerifier;
use App\Service\OrderDetailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    public function __construct(private readonly EmailVerifier $emailVerifier)
    {
    }
    #[Route('/informations', name: 'app_informations')]
    public function informations(
        Request                     $request,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
//        SendMailService             $mailService,
//        JWTService                  $jwt,
        UsersRepository              $usersRepository
    ): Response
    {

        $user = $this->getUser();


        /* **Formulaire informations** */

        //on génère le formulaire
        $formInfosUser = $this->createForm(InfosUserType::class);
        //on alimente les champs avec les données de l'entité User
        $formInfosUser->setData($user);

        $oldEmail = $user->getEmail();
        $formInfosUser->handleRequest($request);
        if ($formInfosUser->isSubmitted() && $formInfosUser->isValid()) {
            $message = "Vos informations on été mais à jour.";

            if ($oldEmail !== $formInfosUser->get('email')->getData()) {


                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('inscription@monsite.com', 'Inscription'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('emailsTemplates/confirmation_email.html.twig')
                );
                $user->setIsVerified(false);
                $message = "Vos informations on été mais à jour. Un email a été envoyé pour confirmer l'adresse email";
            }


            $entityManager->persist($user); //of whatever the entity object you're using to create the form1 form
            $entityManager->flush();

            $this->addFlash('successMessageFlash', $message);
            return $this->redirectToRoute('app_informations');
        }

        /* Formulaire mot de passe */
        $formPassword = $this->createForm(ModifyPasswordType::class);

        $formPassword->handleRequest($request);
        if ($formPassword->isSubmitted() && $formPassword->isValid()) {

            if (!$userPasswordHasher->isPasswordValid($user, $formPassword->get('oldPassword')->getData())) {
                $this->addFlash('danger', 'Mot de passe actuel incorrect');
            } else {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $formPassword->get('newPassword')->getData()
                    )
                );
                $this->addFlash('successMessageFlash', 'Mot de passe modifié avec succès');
            }

            $entityManager->persist($user); //of whatever the entity object you're using to create the form2 form
            $entityManager->flush();
        }

        return $this->render('account/pages/informations.html.twig', [
            'formInfosUser' => $formInfosUser->createView(),
            'formPassword' => $formPassword->createView(),
        ]);
    }

    #[Route('/commandes', name: 'app_order_account')]
    public function orders(OrdersDetailsRepository $ordersDetailsRepository, OrderDetailService $orderDetailService, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $orders = $user->getOrders();
        $datasOrders = [];
        foreach ($orders as $order) {
            //je récupère l'id de la commande
            $id = $order->getId();


            // Récupération des détails de la commande pour calculer le prix total
            $orderDetails = $ordersDetailsRepository->findBy(['orders' => $order]);
            $totalPrice = 0;
            foreach ($orderDetails as $detail) {
                $totalPrice += $detail->getPrice() * $detail->getQuantity();
            }
//            dd($order->getOrderDate());
            //on insérère dans "$datasOrders" un array avec la référence + date + prix total de la commande
            $datasOrders[] = [
                'reference' => $order->getReference(),
                'orderDate' => $order->getOrderDate(),
                'orderStatus' => $order->getStatus(),
                'totalPrice' => $totalPrice

            ];
        }



        return $this->render('account/pages/order.html.twig', [
            'datasOrders' => $datasOrders,
        ]);
    }

    #[Route('/commandes/{reference}', name: 'app_orderDetails_account')]
    public function orderDetails(Orders $orders, OrderDetailService $orderDetailService): Response
    {
        $orderDetails = $orderDetailService->getOrderDetails($orders->getId());
//        dd($orderDetails);
        return $this->render('account/pages/orderDetails.html.twig', compact("orderDetails"));
    }




    #[Route('/adresses', name: 'app_addresses')]
    public function addresses(
        ShippingAddressesRepository $shippingAddressesRepository, 
        BillingAddressesRepository $billingAddressesRepository): Response
    {
        $secondaryAddresses = [];
        $mainAddress = [];

        $shippingaddresses = $shippingAddressesRepository->findByUser($this->getUser());
        $billingAddress = $billingAddressesRepository->findByUser($this->getUser())[0] ?? [];

        foreach ($shippingaddresses as $address) {
            //si c'est une adresse principale, on l'ajoute au tableau $mainAddress, sinon à $secondaryAddresses
            $address->getIsMain() ? $mainAddress = $address : $secondaryAddresses[] = $address;
        }
        return $this->render('account/pages/addresses.html.twig', compact('mainAddress', 'secondaryAddresses', 'billingAddress'));
    }

    #[Route('/adresses/ajouter', name: 'app_add_address')]
    public function addAddresses(
        Request                  $request,
        EntityManagerInterface   $entityManager,
        ShippingAddressesRepository $shippingAddressesRepository,
        BillingAddressesRepository $billingAddressesRepository
    ): Response
    {

        //On considère que par défaut le client n'a pas d'adresse,
        //Donc la checkbox "Enregistrer comme adresse préférée" sera disabled :
        $checkboxActivated = false;
        // on veut savoir s'il en possède : voir autre commentaire dans la vue twig
        if ($shippingAddressesRepository->findByUser($this->getUser())) {
            $checkboxActivated = true;
        }

        $address = new ShippingAddresses();
        $formAddress = $this->createForm(ShippingAddressesType::class, $address);
        $formAddress->handleRequest($request);

        if ($formAddress->isSubmitted() && $formAddress->isValid()) {

            //On attribue à l'adresse le UserID
            $address->setUser($this->getUser());


            // ...Si pas d'adresse de facturation,
            if (!$billingAddressesRepository->findByUser($this->getUser())) {

                //On l'ajoute dans la table adresse de facturation
                $addressBilling = new BillingAddresses();
                $addressBilling->setUser($this->getUser());
                $addressBilling->setZipcode($formAddress->get('zipcode')->getData());
                $addressBilling->setCity($formAddress->get('city')->getData());
                $addressBilling->setAddress($formAddress->get('address')->getData());
                $addressBilling->setFirstname($formAddress->get('firstname')->getData());
                $addressBilling->setLastname($formAddress->get('lastname')->getData());
                $addressBilling->setIsMain(true);

//                $addressBilling->setAdditional($formAddress->get('additional')->getData());
                $entityManager->persist($addressBilling);
            }

            //si le client à " la main" sur la checkbox
            if ($checkboxActivated) {
                //On va vérifier si le client a coché la checkbox
                if ($formAddress->get('isMain')->getData()) {
                    // On recherche son adresse principale...
                    $adresseMain = $shippingAddressesRepository->findOneBy([
                        'user' => $this->getUser(),
                        'isMain' => true
                    ]);
                    //À condition qu'elle existe, on la passe à false, ainsi pas de doublons d'adresses principales
                    $adresseMain?->setIsMain(false);
                }
            } else {
                $address->setIsMain(true);
            }

            $entityManager->persist($address);
            $entityManager->flush();
            $this->addFlash('successMessageFlash', "Adresse ajoutée avec succès !");

            return $this->redirectToRoute('app_addresses');
        }

        return $this->render('account/pages/form.html.twig', [
            'formAddress' => $formAddress->createView(),
            'checkboxActivated' => $checkboxActivated,
            'title' => 'Ajouter votre adresse',
            'action' => 'Ajouter'
        ]);
    }

    #[Route('/adresses/edit/{id}', name: 'app_edit_address')]
    public function editAddresses(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ShippingAddressesRepository $shippingAddressesRepository
    ): Response
    {
        //par défaut, le client n'a pas encore l'autorisation d'éditer son adresse.
        $permission = false;
        $mainAddress = [];
        //on récupère les adresses du client :
        $arrayAddress = $shippingAddressesRepository->findByUser($this->getUser());

        foreach ($arrayAddress as $address) {
            //si l'id de l'adresse appartient bien au client
            if ($address->getId() === (int)$id) {
                $permission = true;
            }
            //au passage, on récupère son adresse principale
            if ($address->getIsMain()) {
                $mainAddress = $address;
            }
        }
        if ($permission) {
            //On récupère l'adresse à modifier
            $address = $shippingAddressesRepository->find($id);

            $checkboxActivated = false;
            //si le client a plus d'une adresse dans son carnet, on lui donne la possibilité de cocher la checkbox
            if (count($arrayAddress) > 1) {
                $checkboxActivated = true;
            }
            $formAddress = $this->createForm(ShippingAddressesType::class, $address);
            $formAddress->setData($formAddress);

            $formAddress->handleRequest($request);

            if ($formAddress->isSubmitted() && $formAddress->isValid()) {
                //si la checkbox était "disabled" par défaut, ce sera donc toujours une adresse principale
                //et même si le client décoche la checkbox depuis l'inspecteur.
                if (!$checkboxActivated) {
                    $address->setIsMain(true);
                } else {
                    //Autrement le client a le choix pour la basculer en adresse principale ou secondaire,
                    //en fonction de la valeur de la checkbox.
                    //Si le client a coché ET que son adresse principale est différente de celle qu'il souhaite modifier, alors
                    //on la passe à False, ainsi pas de doublons d'adresses principales.
                    if ($formAddress->get('isMain')->getData() && $mainAddress->getId() !== (int)$id) {
                        $mainAddress->setIsMain(false);
                    }
                }

                $entityManager->persist($address);
                $entityManager->flush();
                $this->addFlash('successMessageFlash', "Adresse modifiée avec succès !");
                return $this->redirectToRoute('app_addresses');
            }


            return $this->render('account/pages/form.html.twig', [
                'formAddress' => $formAddress->createView(),
                'checkboxActivated' => $checkboxActivated,
                'title' => 'Modifier votre adresse',
                'action' => 'Modifier'
            ]);
        } else {
            $this->addFlash('danger', "Vous ne pouvez pas modifier cette adresse.");
            return $this->redirectToRoute('app_addresses');
        }

    }

    #[Route('/adresses/editFacturation/{id}', name: 'app_edit_billing')]
    public function editBilling(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        BillingAddressesRepository $billingAddressesRepository
    ): Response
    {
        //On récupère l'adresse à modifier
        $addressBilling = $billingAddressesRepository->find($id);
        if ($addressBilling && $addressBilling->getUser() === $this->getUser()) {

            $formAddressBilling = $this->createForm(BillingAddressesType::class, $addressBilling);
//            $formAddressBilling->setData($addressBilling);

            $formAddressBilling->handleRequest($request);
//            dd($request);
            if ($formAddressBilling->isSubmitted() && $formAddressBilling->isValid()) {


                $entityManager->persist($addressBilling);
                $entityManager->flush();
                $this->addFlash('successMessageFlash', "Adresse de facturation modifiée avec succès !");
                return $this->redirectToRoute('app_addresses');
            }


            return $this->render('account/pages/form.html.twig', [
                'formAddressBilling' => $formAddressBilling->createView(),
                'title' => 'Modifier votre adresse de facturation',
                'action' => 'Modifier',
                'editBilling' => true
            ]);
        }
        $this->addFlash('danger', "Erreur !");
        return $this->redirectToRoute('app_addresses');

    }

    #[Route('/adresses/delete/{id}', name: 'app_delete_address')]
    public function deleteAddress(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ShippingAddressesRepository $shippingAddressesRepository
    ): Response
    {
        $permission = false;

        $arrayAddress = $shippingAddressesRepository->findByUser($this->getUser());
        foreach ($arrayAddress as $address) {
            //si l'id de l'adresse appartient bien au client
            if ($address->getId() === (int)$id) {
                $permission = true;
            }
        }
        if ($permission) {
            $address = $shippingAddressesRepository->find($id);
            $entityManager->remove($address);
            $entityManager->flush();
            $this->addFlash('successMessageFlash', "Suppression de l'adresse réalisée avec succès !");
            return $this->redirectToRoute('app_addresses');
        }
        $this->addFlash('danger', "Vous n'avez pas l'autorisation pour supprimer cette adresse !");
        return $this->redirectToRoute('app_addresses');

    }

    #[Route('/adresses/toggle/{id}', name: 'app_toggle_address')]
    public function toggleAddresses(
        $id,
        EntityManagerInterface $entityManager,
        ShippingAddressesRepository $shippingAddressesRepository
    ): Response
    {
        $address = $shippingAddressesRepository->find($id);
        $adresseMain = $shippingAddressesRepository->findOneBy([
            'user' => $this->getUser(),
            'isMain' => true
        ]);
        //et on la passe à false, ainsi pas de doublons d'adresses principales
        if ($adresseMain) {
            $adresseMain->setIsMain(false);
        }
        $address->setIsMain(true);
        $entityManager->flush();
        $this->addFlash('successMessageFlash', "Adresse préférée ajoutée avec succès !");
        return $this->redirectToRoute('app_addresses');

    }

    #[Route('/favoris', name: 'app_favourites')]
    public function favourites(): Response
    {
        return $this->render('account/pages/favourites.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    #[Route('/bonsAchat', name: 'app_vouchers')]
    public function vouchers(): Response
    {
        return $this->render('account/pages/vouchers.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }
}
