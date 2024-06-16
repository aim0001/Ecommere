<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function index(CategoryRepository $categoryRepository, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil active'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
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


        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);



        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
            'menuItems' => $menuItems,
            'categories'=> $categoryRepository->findAll(),
            'form'=>$form->createView(),
            'total'=>$total
        ]);
    }

    #[Route('/city/{id}/shipping/cost/', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status'=>200, "message"=>"on", 'content'=>$cityShippingPrice]));
    }
}
