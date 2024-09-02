<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Services\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class StripeController extends AbstractController
{
    #[Route('/pay/success', name: 'app_stripe_success')]
    public function success(Cart $cart, SessionInterface $session): Response
    {

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'A propos', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        $session->set('cart', []);

        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/pay/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'A propos', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/stripe/notify', name: 'app_stripe_notify')]
    public function stripeNotify(Request $request, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);

        $endpoint_secret = 'whsec_fb0545b0f686a52982c5fc97cd779e0c64602409bb51c8552c974864d78079a1';

        $payload = $request->getContent();

        $sig_header = $request->headers->get('stripe-signature');

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('payload invalide', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('signature invalide');
        }

        switch ($event->type) {
            case 'payment_intent.succeeded': //Contient l objet payment_intent

                $paymentIntent = $event->data->object;

                $fileName = 'stripe-details-' . uniqid() . '.txt';

                $commandeId = $paymentIntent->metadata->commandeId;

                $commande = $commandeRepository->find($commandeId);

                $cartPrice = $commande->getTotalPrice();
                $stripeTotalAmount = $paymentIntent->amount;


                if ($cartPrice == $stripeTotalAmount) {
                    $commande->setPaymentCompleted(1);
                    $entityManager->flush();
                }

                // file_put_contents($fileName, $commandeId);
                break;
            case 'payment_method.attached': //Contient l objet payment_method
                $paymentMethod = $event->data->object;
                break;

            default:
                # code...
                break;
        }

        return  new Response('evenement recu', 200);
    }
}
