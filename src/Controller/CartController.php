<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\BillingAddressesRepository;
use App\Repository\ProductsRepository;
use App\Repository\ShippingAddressesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier', name: 'app_cart_')]
class CartController extends AbstractController
{


    #[Route('/', name: 'index')]
    public function index(
        SessionInterface $session,
        ProductsRepository $productsRepository,
        ShippingAddressesRepository $shippingAddressesRepository,
        BillingAddressesRepository $billingAddressesRepository
    ): Response
    {

        $cart = $session->get('cart', []);
        $dataCart = [];
        $total = 0;

        foreach($cart as $id => $quantity){
            if (is_int($id)){
                $product = $productsRepository->find($id);

                $dataCart[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }

        }
        $shippingAddress = null;
        $billingAddress = null;
        if ($this->getUser()){
            $shippingAddress = $shippingAddressesRepository->findOneBy([
                'user' => $this->getUser(),
                'isMain' => true
            ]);
            $billingAddress = $billingAddressesRepository->findOneBy([
                'user' => $this->getUser(),
                'isMain' => true
            ]);
        }
        return $this->render('cart/cart.html.twig', compact('dataCart', 'total', 'shippingAddress', 'billingAddress'));
    }

    /**
     * @param Products $products
     * @param SessionInterface $session
     * @return Response
     * //Pour ajouter un produit
     */
    #[Route('/add/{id}', name: 'add')]
    public function add(Products $products, SessionInterface $session): Response
    {
        $id = $products->getId();
        $cart = $session->get('cart', []);
        // On ajoute le produit dans le panier s'il n'y est pas encore
        // Sinon on incrémente sa quantité
        empty($cart[$id]) ? $cart[$id] = 1 : $cart[$id]++;
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart_index');


    }

    /**
     * @param Products $products
     * @param SessionInterface $session
     * @return RedirectResponse
     * //pour retirer un produit
     */
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Products $products, SessionInterface $session): RedirectResponse
    {
        //On récupère l'id du produit
        $id = $products->getId();

        // On récupère le panier existant
        $cart = $session->get('cart', []);

        // On retire le produit du panier s'il n'y a qu'1 exemplaire
        // Sinon on décrémente sa quantité
        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $session->set('cart', $cart);

        //On redirige vers la page du panier
        return $this->redirectToRoute('app_cart_index');
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Products $products, SessionInterface $session): RedirectResponse
    {
        //On récupère l'id du produit
        $id = $products->getId();

        // On récupère le panier existant
        $cart = $session->get('cart', []);

        if(!empty($cart[$id])){
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        //On redirige vers la page index
        return $this->redirectToRoute('app_cart_index');
    }
    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session): RedirectResponse
    {
        $session->remove('cart');

        return $this->redirectToRoute('app_cart_index');
    }
}
