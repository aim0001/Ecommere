<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Commande;
use App\Entity\Purchases;
use App\Form\CommandeType;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\ProductRepository;
use App\Repository\PurchasesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function index(CategoryRepository $categoryRepository, CityRepository $city, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
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

        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        return $this->render('commande/index.html.twig', [
            'menuItems' => $menuItems,
            'categories' => $categoryRepository->findAll(),
            'form' => $form->createView(),
            'total' => $total,
            'city' => $city->findAll(),
            'user' => $this->getUser()
        ]);
    }

    #[Route('/city/{id}/shipping/cost/', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status' => 200, 'message' => 'Success', 'content' => $cityShippingPrice]));
    }

    #[Route('/commande/verify', name: 'app_commande_prepare', methods: ['GET'])]
public function prepareOrder(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
{
    $transaction_id = $request->query->get('transaction_id');
    $public_key = "30a3f5f0316e11efb3f2d358b3ba43e1";
    $private_key = "tpk_30a3f5f2316e11efb3f2d358b3ba43e1";
    $secret = "tsk_30a3f5f3316e11efb3f2d358b3ba43e1";
    $kkiapay = new \Kkiapay\Kkiapay($public_key, $private_key, $secret, true);

    $exist = $kkiapay->verifyTransaction($transaction_id);

    if ($exist->status != "SUCCESS") {
        // Gérer l'erreur si la transaction n'est pas réussie
        $this->addFlash('error', 'Transaction échouée.');
        return $this->redirectToRoute('app_commande');
    }

    $adresse = explode(',', $exist->state)[0];
    $phone = explode(',', $exist->state)[1];
    $cityName = explode(',', $exist->state)[2];
    

    // Récupérer la ville depuis le repository City
    $city = $entityManager->getRepository(City::class)->findOneBy(['id' => $cityName]);


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

    $user = $this->getUser();
    $commande = new Commande();
    $commande->setCreatedAt(new \DateTimeImmutable());
    $commande->setUser($user);
    $commande->setCity($city);
    $commande->setPhone($phone);
    $commande->setAdresse($adresse);
   
    $entityManager->persist($commande);
    $entityManager->flush();

    foreach ($cart as $id => $quantity) {
        $product = $productRepository->find($id);
        if ($product) {
            $achat = new Purchases();
            $achat->setProduct($product);
            $achat->setQuantity($quantity);
            $achat->setMontantTotal($total);
            $commande->addPurchase($achat);
            $entityManager->persist($achat);
        }
    }

    $entityManager->flush();

    $session->set('commande', $commande);
    

    return $this->redirectToRoute('app_commande_recap');
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

    #[Route('/commande/valider', name: 'app_commande_valider', methods: ['GET'])]
    public function validerCommande(SessionInterface $session, EntityManagerInterface $entityManager): Response
    {

        // Logique pour valider la commande, comme vider le panier, mettre à jour les états des produits, etc.
        $session->remove('cart');
        $session->remove('productStates');
        // Logique pour persister les données de la commande si nécessaire

        return $this->redirectToRoute('menu_Boutique');
    }

    #[Route('/editor/commande/{id}', name: 'admin_commande_detail', methods: ['GET'])]
    public function adminOrderDetail(Commande $commande, PurchasesRepository $purchasesRepository): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];



        return $this->render('commande/admin_commande_detail.html.twig', [
            'menuItems' => $menuItems,
            'commande' => $commande,
            'produitsCommandes' => $purchasesRepository->findByCommande($commande),
            'achat' => $purchasesRepository
        ]);
    }
}
