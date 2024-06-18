<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function index(CategoryRepository $categoryRepository, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        $cart = $session->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        $total = array_sum(array_map(function ($item) {
            return $item['product']->getPrice() * $item['quantity'];
        }, $cartWithData));

        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
            'menuItems' => $menuItems,
            'categories' => $categoryRepository->findAll(),
            'form' => $form->createView(),
            'total' => $total
        ]);
    }

    #[Route('/city/{id}/shipping/cost/', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status' => 200, 'message' => 'Success', 'content' => $cityShippingPrice]));
    }

    #[Route('/commande/verify', name: 'app_commande_prepare', methods: ['POST'])]
    public function prepareOrder(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        $commande = new Commande();
        
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setCreatedAt(new \DateTimeImmutable());
            
            // Récupérez l'utilisateur actuellement connecté
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute('app_login');
            }
            
            // Associez l'utilisateur à la commande
            $commande->setUser($user);
    
            $entityManager->persist($commande);
            $entityManager->flush();
    
            $session->set('commande', $commande);
    
            // Redirigez selon les besoins après l'enregistrement de la commande
            return $this->redirectToRoute('app_commande_recap');
        }
    
        // Gérez le cas où le formulaire n'est pas valide
        return $this->render('commande/index.html.twig', [
            'menuItems' => $menuItems,
            'form' => $form->createView()
        ]);
    }

    #[Route('/commande/recap', name: 'app_commande_recap')]
    public function orderRecap(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        $commande = $session->get('commande');

        if (!$commande) {
            return $this->redirectToRoute('app_commande');
        }

        $cart = $session->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        $total = array_sum(array_map(function ($item) {
            return $item['product']->getPrice() * $item['quantity'];
        }, $cartWithData));

        return $this->render('commande/recap.html.twig', [
            'menuItems' => $menuItems,
            'commande' => $commande,
            'cartWithData' => $cartWithData,
            'total' => $total
        ]);
    }

    #[Route('/editor/commandes', name: 'admin_commande_list')]
    public function adminOrderList(EntityManagerInterface $entityManager): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        $commandes = $entityManager->getRepository(Commande::class)->findAll();

        return $this->render('commande/admin_commande_list.html.twig', [
            'menuItems' => $menuItems,
            'commandes' => $commandes
        ]);
    }

    #[Route('/editor/commande/{id}', name: 'admin_commande_detail')]
    public function adminOrderDetail(Commande $commande): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('commande/admin_commande_detail.html.twig', [
            'menuItems' => $menuItems,
            'commande' => $commande,
        ]);
    }
}
