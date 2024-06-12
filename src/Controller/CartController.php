<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{

    public function __construct(private readonly ProductRepository $productRepository)
    {
        
        
    }

    #[Route('/cart', name: 'app_cart', methods:['GET'])]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, SessionInterface $session, Request $request, PaginatorInterface $paginator): Response
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
                'product' => $this->productRepository->find($id),
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

        // dd($cartWithData);

        return $this->render('menu/boutique.html.twig', [
            // 'itemPaginate' => $cartPagination,
            'items' => $cartWithData,
            'total' => $total,
            'menuItems' => $menuItems,
            'products' => $products,
            'categories'=> $categoryRepository->findAll()
        ]);
    }


    //Ajouter un element au panier
    #[Route('/cart/add/{id}/', name: 'app_cart_new', methods:['GET'])]
    public function addToCart(int $id, SessionInterface $session, Request $request):Response
    {

        $cart = $session->get('cart',[]);
        if (!empty($cart[$id])) {
            $cart[$id]++;
        }else {
            $cart[$id] = 1;
        }

        $session->set('cart',$cart);

        $redirect = $request->query->get('redirect', null);
            if ($redirect) {
                return $this->redirect($redirect);
            }

        return $this->redirectToRoute('app_cart');

    }

    //Supprimer un produit
    #[Route('/cart/remove/{id}/', name: 'app_cart_product_remove', methods: ['GET'])]
    public function removeToCart(int $id, SessionInterface $session, Request $request): Response
    {
        $cart = $session->get('cart', []);
        
        if (!empty($cart[$id])) {
            
                unset($cart[$id]);  
            
        }

        $session->set('cart', $cart);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'success', 'productId' => $id]);
        }

        return $this->redirectToRoute('app_cart');
    }

    //Supprimer tout les produits ajouté
    #[Route('/cart/remove/', name: 'app_cart_remove', methods: ['GET'])]
    public function removeFromCart(SessionInterface $session, Request $request): Response
    {
        $session->set('cart',[]);

        if ($request->isXmlHttpRequest()) {
        return new JsonResponse(['status' => 'success']);
    }

        return $this->redirectToRoute('app_cart');
    }
}
