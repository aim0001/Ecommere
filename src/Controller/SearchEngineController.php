<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Services\Cart;
use Knp\Component\Pager\PaginatorInterface;
use Stripe\BillingPortal\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchEngineController extends AbstractController
{
    #[Route('/search/engine', name: 'app_search_engine', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Aperçus', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique active']
        ];

        if ($request->isMethod('GET')) {
            $data = $request->query->all();
            $word = $data['word'];

            $results = $productRepository->searchEngine($word);
        }
        return $this->render('search_engine/index.html.twig', [
            'products' => $results,
            'menuItems' => $menuItems,
            'categories'=> $categoryRepository->findAll(),
            'word'=>$word
        ]);
    }
}
