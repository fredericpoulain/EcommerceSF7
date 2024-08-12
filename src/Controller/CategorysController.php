<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ProductsRepository;
use App\Service\BreadcrumbService;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategorysController extends AbstractController
{
    #[Route('/categorie/{slug}', name: 'app_categorys')]
    public function index(Categories $categories, BreadcrumbService $breadcrumbService): Response
    {

        $dataBreadcrumb = $breadcrumbService->getData($categories);

        $productsListContainsItems = !empty($categories->getProducts()->toArray());
        if ($productsListContainsItems){
            return $this->redirectToRoute('app_productsList', [
                'slug' => $categories->getSlug(),
            ]);
        }
        return $this->render('categorys/categorys.html.twig', [
            'category' => $categories,
            'productsListContainsItems' => $productsListContainsItems,
            'dataBreadcrumb' => $dataBreadcrumb,
        ]);

    }
    #[Route('/liste-produits/{slug}', name: 'app_productsList')]
    public function productsList(
        Categories              $categories,
        Request                $request,
        PaginatorInterface $paginator,
        BreadcrumbService $breadcrumbService

    ): Response
    {
        $products = $categories->getProducts()->toArray();
        if ($products){
            //Récupération de la valeur de "page" dans l'URL. Si non-présente, 1 par défaut
            $page = $request->query->getInt('page', 1);
            //On fixe une limite de 4 projets par page
            $limit = 8;
            //On récupère les products paginés via KNP_Paginator
            $productsPaginated =  $paginator->paginate(
                $products,
                $page,
                $limit
            );
            //page maximale : nombreDeProjets / limiteParPage → arrondi au supérieur.
            $maxPage = ceil($productsPaginated->getTotalItemCount() / $limit);
            //si on tente d'accéder à une page supérieure à la page maximale, on redirige.

            if ($page > $maxPage) return $this->redirectToRoute('app_productsList', ['slug' => $categories->getSlug()]);

            $dataBreadcrumb = $breadcrumbService->getData($categories);
        }
        return $this->render('categorys/productsList.html.twig', [
            'title' => $categories->getName(),
            'products' => $productsPaginated ?? null,
            'dataBreadcrumb' => $dataBreadcrumb ?? null,
        ]);

    }


}
