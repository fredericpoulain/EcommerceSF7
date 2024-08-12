<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class HomeController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_home')]
    public function index(ProductsRepository $productsRepository): Response
    {

        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('infoMessageFlash', "Connectez-vous grâce aux champs préremplis !");
        }

        if (!is_null($user) && !$user->getIsVerified()){
            $link = "<a href='/renvoi-verification/' style='text-decoration: underline'>CE LIEN</a>";
            $this->addFlash('infoMessageFlash', "Votre compte n'est pas encore activé. cliquez sur " . $link . " pour recevoir un nouveau mail de validation");
        }
        $randProducts = $productsRepository->findRandomProduct();
//        $categories = $categorieRepository->findAll();
        return $this->render('home/home.html.twig', [
            'randProducts' => $randProducts
        ]);
    }
}
