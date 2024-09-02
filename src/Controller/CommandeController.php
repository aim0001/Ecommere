<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Commande;
use App\Entity\ProductCommande;
use App\Form\CommandeType;
use App\Repository\CategoryRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProductRepository;
use App\Services\Cart;
use App\Services\StripePayment;
use DateTime;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\TextUI\Command;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class CommandeController extends AbstractController
{

    public function __construct(private MailerInterface $mailer) {}

    #[Route('/commande', name: 'app_commande')]
    public function index(CategoryRepository $categoryRepository, Request $request, SessionInterface $session, Cart $cart, EntityManagerInterface $entityManager): Response
    {

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'A propos', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];



        $data = $cart->getCart($session);


        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            
                if (!empty($data['total'])) {
                    $totalPrice = $data['total'] + $commande->getCity()->getShippingCost();
                    $commande->setTotalPrice($totalPrice);
                    $commande->setCreatedAt(new \DateTimeImmutable());
                    $commande->setPaymentCompleted(0);
                    $entityManager->persist($commande);
                    $entityManager->flush();


                    foreach ($data['cart'] as $value) {
                        $commandeProduct = new ProductCommande();
                        $commandeProduct->setCommande($commande);
                        $commandeProduct->setProduct($value['product']);
                        $commandeProduct->setQte($value['quantity']);

                        $entityManager->persist($commandeProduct);
                        $entityManager->flush();
                    }

                    if ($commande->isPayOnDelivery()){

                        $session->set('cart', []);

                        $html = $this->renderView('mail/commandeConfirm.html.twig', [
                            'commande' => $commande
                        ]);
        
                        $email = (new Email())
                            ->from('tbelbois@gmail.com')
                            ->to($commande->getEmail())
                            ->subject('Confirmation de reception de la commande')
                            ->html($html);
        
                            $this->mailer->send($email);
                            
                        return $this->redirectToRoute('commande_ok_message');

                    }

                    $payment = new StripePayment();

                    $shippingCost = $commande->getCity()->getShippingCost();

                    $payment->startPayment($data, $shippingCost, $commande->getId());
        
                    $stripeRedirectUrl = $payment->getStripeRedirectUrl();
        
                    return $this->redirect($stripeRedirectUrl);

                }

        }


        return $this->render('commande/index.html.twig', [
            'controller_name' => 'CommandeController',
            'menuItems' => $menuItems,
            'categories' => $categoryRepository->findAll(),
            'form' => $form->createView(),
            'total' => $data['total']
        ]);
    }

    #[Route('/city/{id}/shipping/cost/', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status' => 200, "message" => "on", 'content' => $cityShippingPrice]));
    }

    #[Route('/editor/commande/{type}/', name: 'app_commandes_shows')]
    public function getAllOrder($type, CommandeRepository $commandeRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'A propos', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        if ($type == 'is-completed') {
            $data = $commandeRepository->findBy(['isCompleted'=>1], ['id' => 'DESC']);   
        }elseif ($type == 'not-completed') {
            $data = $commandeRepository->findBy(['isCompleted'=>null,'payOnDelivery'=>1], ['id' => 'DESC']);
        }elseif ($type == 'pay-on-stripe-not-delivered') {
            $data = $commandeRepository->findBy(['isCompleted'=>null,'payOnDelivery'=>0, 'isPaymentCompleted'=>1], ['id' => 'DESC']);
        }elseif ($type == 'pay-on-stripe-is-delivered') {
            $data = $commandeRepository->findBy(['isCompleted'=>1,'payOnDelivery'=>0, 'isPaymentCompleted'=>1], ['id' => 'DESC']);
        }else{
            $data = $commandeRepository->findBy([], ['id' => 'DESC']);
        }

        $commande =  $paginator->paginate(
            $data,  // Page a paginer
            $request->query->getInt('page', 1), // Le numero de la page par defaut est 1
            2 // Max par page
        );

        return $this->render('commande/commande.html.twig', [
            'menuItems' => $menuItems,
            'commandes' => $commande
        ]);
    }

    #[Route('/editor/commande/{id}/is-completed/update', name: 'app_orders_is_completed_update')]
    public function isCompleted($id, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $commande = $commandeRepository->find($id);
        $commande->setCompleted(true);
        $entityManager->flush();
        $this->addFlash('success', 'modification effectuée');

        return $this->redirect($request->headers->get('referer'));
    }


    #[Route('/editor/commande/{id}/remove', name: 'app_commandes_remove')]
    public function removeCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($commande);
        $entityManager->flush();
        $this->addFlash('danger', 'Une commande a été supprimée');

        return $this->redirectToRoute('app_commandes_shows');
    }

    #[Route('/commande-ok-message', name: 'commande_ok_message')]
    public function commandeMessage(): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'A propos', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('commande/commande_message.html.twig', [
            'menuItems' => $menuItems,
        ]);
    }

}
