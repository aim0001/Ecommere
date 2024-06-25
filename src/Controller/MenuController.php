<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    #[Route('/', name: 'menu_Accueil')]
    public function index(CategoryRepository $categoryRepository): Response
    {

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil active'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];


        return $this->render('menu/index.html.twig', [
            'menuItems' => $menuItems,
            
        ]);
    }


    #[Route('/galerie', name: 'menu_Galerie')]
    public function gallerie(CategoryRepository $categoryRepository): Response
    {

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie active'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];


        return $this->render('menu/galerie.html.twig', [
            'menuItems' => $menuItems,
            'categories'=> $categoryRepository->findAll(),
        ]);
    }


    #[Route('/boutique', name: 'menu_Boutique', methods: ['GET'])]
    public function boutique(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator, Session $session): Response
    {

        $data = $productRepository->findBy([],['id'=>"DESC"]);
        $products =  $paginator->paginate(
            $data,  // Page a paginer
            $request->query->getInt('page', 1), // Le numero de la page par defaut est 1
            9 // Max par page
        );
        
        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique active']
        ];


        $cart = $session->get('cart',[]);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        // $cartPagination = $paginator->paginate(
        //     $cartWithData,
        //     $request->query->getInt('cartPage',1),
        //     4
        // );

        $total = array_sum(array_map(function($item){
            return $item['product']->getPrice() * $item['quantity'];
        },$cartWithData));


        return $this->render('menu/boutique.html.twig', [
            // 'itemPaginate' => $cartPagination,
            'items' => $cartWithData,
            'total' => $total,
            'menuItems' => $menuItems,
            'products' => $products,
            'categories'=> $categoryRepository->findAll()
        ]);
    }

    #[Route('/home/product/{id}/show', name: 'app_home_product_show', methods: ['GET'])]
    public function show(Product $product, ProductRepository $productRepository, CategoryRepository $categoryRepository, Session $session): Response
    {

        $lastProducts = $productRepository->findBy([],['id'=> 'DESC'], 5);

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique active']
        ];

        $cart = $session->get('cart',[]);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        $total = array_sum(array_map(function($item){
            return $item['product']->getPrice() * $item['quantity'];
        },$cartWithData));

        return $this->render('menu/show.html.twig', [
            'items' => $cartWithData,
            'total' => $total,
            'menuItems' => $menuItems,
            'product' => $product,
            'categories'=> $categoryRepository->findAll(),
            'products' => $lastProducts
        ]);
    }


    #[Route('/home/product/subCategory/{id}/filter', name: 'app_home_product_filter', methods: ['GET'])]
    public function filter($id, SubCategoryRepository $subCategoryRepository, CategoryRepository $categoryRepository, ProductRepository $productRepository, Session $session): Response
    {

        $products = $subCategoryRepository->find($id)->getProducts();
        $subCategory = $subCategoryRepository->find($id);  

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique active']
        ];

        $cart = $session->get('cart',[]);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        $total = array_sum(array_map(function($item){
            return $item['product']->getPrice() * $item['quantity'];
        },$cartWithData));

        return $this->render('menu/filter.html.twig', [
            'items' => $cartWithData,
            'total' => $total,
            'menuItems' => $menuItems,
            'products' => $products,
            'categories'=> $categoryRepository->findAll(),
            'subCategory'=> $subCategory,   
        ]);
    }
}
