<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Products;
use App\Form\CommentsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    #[Route('/produits/{slug}', name: 'app_products')]
    public function index(Products $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comments();

        $commentForm = $this->createForm(CommentsType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()){

            $comment->setProduct($product);
            $comment->setUser($this->getUser());
            $comment->setTitle($commentForm->get('title')->getData());
            $comment->setText($commentForm->get('text')->getData());
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_products', ['slug' => $product->getSlug()]);
        }

        $imagesArray = $product->getImages()->toArray();
        $comments = $product->getComments()->toArray();
        return $this->render('products/products.html.twig', [
            'product' => $product,
            'imagesArray'=>$imagesArray,
            'commentForm' => $commentForm->createView(),
            'comments' => $comments,
        ]);
    }
}
