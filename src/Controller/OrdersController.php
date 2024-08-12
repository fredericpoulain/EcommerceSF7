<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Repository\BillingAddressesRepository;
use App\Repository\ProductsRepository;
use App\Repository\ShippingAddressesRepository;
use App\Repository\UsersRepository;
use App\Service\OrderDetailService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class OrdersController extends AbstractController
{
    private const string STATUS_SUCCESS = 'paid';

    public function __construct(
        private readonly ProductsRepository          $productsRepository,
        private readonly ShippingAddressesRepository $shippingAddressesRepository,
        private readonly BillingAddressesRepository  $billingAddressesRepository,
        private readonly EntityManagerInterface      $em,
        private readonly MailerInterface             $mailer,
    )
    {
    }

    #[Route('/orderCancel', name: 'app_orders_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('infoMessageFlash', 'La commande a été annulée');
        return $this->redirectToRoute('app_home');
    }
    #[Route('/orderSuccess', name: 'app_orders_success')]
    public function orderSuccess(SessionInterface $session): Response
    {
        $this->addFlash('successMessageFlash', "Commande validé ! une confirmation viens d'être envoyé sur votre adresse email");
        $session->remove('cart');
        return $this->redirectToRoute('app_order_account');
    }

    /**
     * @throws ApiErrorException
     */
    #[Route('/stripeSession/', name: 'app_stripe_session')]
    public function index(SessionInterface $sessionInterface): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $secretKeyStripe = $this->getParameter('app.secreteKeyStripe');
        \Stripe\Stripe::setApiKey($secretKeyStripe);
        \Stripe\Stripe::setApiVersion('2024-06-20');
        $cart = $sessionInterface->get('cart', []);

        if ($cart === []) {
            $this->addFlash('message', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart_index');
        }

        $user = $this->getUser();

        if (!$user->getIsVerified()) {
            $this->addFlash('errorMessageFlash', 'Compte non activé');
            return $this->redirectToRoute('app_home');
        }

        if (!$user->getShippingAddresses()->toArray()) {
            $this->addFlash('infoMessageFlash', 'Ajoutez une adresse de livraison');
            return $this->redirectToRoute('app_add_address');
        }
        $arrayLineItems = [];
        foreach ($cart as $item => $quantity) {
            if (is_int($item)) {
                $product = $this->productsRepository->find($item);
                $price = $product->getPrice();
                $name = $product->getName();

                $arrayLineItems[] = [
                    'quantity' => $quantity,
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => [
                            'name' => $name,
                        ],
                        'unit_amount' => $price,
                    ]
                ];
            }
        }
        $sessionStripe = Session::create([
            //"mode" : ici, ce sera un "payment". Si ont choisi subscription, c'est pour démarrer un abonnement

            //Si notre application ne gère pas en amont les adresses de livraisons et facturation, on peut demander à stripe de récupérer ces informations.
            //Ensuite, on pourra récupérer ces adresses pour gérer la livraison et la facture.

//            'billing_address_collection' => 'required',
//            'shipping_address_collection' => [
//                'allowed_countries' => ['FR'],
//            ],

            'payment_method_types' => ['card'],
            'line_items' => [$arrayLineItems],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_orders_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_orders_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'payment_intent_data' => [
                'metadata' => [
                    'cart' => json_encode($cart),
                    'userId' => $user->getId(),
                ],
            ],
        ]);

        return $this->redirect($sessionStripe->url);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/stripeWebhook', name: 'app_stripe_webhook')]
    public function stripeWebhook(
        UsersRepository $usersRepository,
        OrderDetailService $orderDetailService,
    ): Response
    {

        $secretKeyStripe = $this->getParameter('app.secreteKeyStripe');
        $endpoint_secret = $this->getParameter('stripe.webhook_secret');

        new \Stripe\StripeClient($secretKeyStripe);
        \Stripe\Stripe::setApiKey($secretKeyStripe);
        \Stripe\Stripe::setApiVersion('2024-06-20');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;


        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        switch ($event->type) {
            case 'payment_intent.payment_failed':
                //logique éventuelle
                break;
            case 'payment_intent.succeeded':
                $event->data->object->metadata->cart;
                //On récupère les produits et les quantités depuis la session Stripe
                $cart = json_decode($event->data->object->metadata->cart);
                $userId = (int)$event->data->object->metadata->userId;
                $user = $usersRepository->find($userId);

                $order = new Orders();
                $this->addOrder($cart, $order, $user, $orderDetailService);
                break;
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return new Response(http_response_code(200));
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function addOrder($cart, $order, $user, $orderDetailService): void
    {

        $order->setUser($user);
        $order->setReference(uniqid());
        $shippingAddress = $this->shippingAddressesRepository->findOneBy([
            'user' => $user,
            'isMain' => true
        ]);
        $billingAddress = $this->billingAddressesRepository->findOneBy([
            'user' => $user,
            'isMain' => true
        ]);
        $order->setShippingAddress($shippingAddress);
        $order->setBillingAddress($billingAddress);

        $order->setStatus(self::STATUS_SUCCESS);

        // On parcourt le panier pour créer les détails de commande

        foreach ($cart as $key => $value) {
            $productId = (int)$key;
            $quantity = (int)$value;

            $orderDetails = new OrdersDetails();

            // On va chercher le produit
            $product = $this->productsRepository->find($productId);

            $price = $product->getPrice();

            // On crée le détail de commande
            $orderDetails->setProducts($product);
            $orderDetails->setPrice($price);
            $orderDetails->setQuantity($quantity);

            $order->addOrdersDetail($orderDetails);

            //Au lieu de "$em->persist($orderDetails);", possibilité d'ajouter cascade: ['persist'] dans l'entité order :
            // #[ORM\OneToMany(mappedBy: 'orders', targetEntity: OrdersDetails::class, orphanRemoval: true, cascade: ['persist'])]
            // private $ordersDetails
            $this->em->persist($orderDetails);
        }

        // On persiste et on flush
        $this->em->persist($order);
        $this->em->flush();

        $this->sendEmail($user, $order->getId(), $orderDetailService);

    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendEmail($user, $orderId, $orderDetailService): void
    {

        $orderDetails = $orderDetailService->getOrderDetails($orderId);
        $firstname = $user->getFirstname();

        $context = compact('firstname', 'orderDetails');
        $email = (new TemplatedEmail())
            ->from('noreply@example.com')
            ->to(new Address($user->getEmail()))
            ->subject('Votre commande')
            ->htmlTemplate('emailsTemplates/confirmation_order.html.twig')
            ->context($context);

        $this->mailer->send($email);
    }

}