<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePayment
{
    private $redirectUrl;

    public function __construct()
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET']);
        Stripe::setApiVersion('2024-06-20');
    }

    public function startPayment($cart, $shippingCost, $commandeId)
    {

        $cartProducts = $cart['cart'];
        $products = [
            [
                'qte' => 1,
                'price' => $shippingCost,
                'name' => "frais de livraison"
            ]
        ];

        foreach ($cartProducts as $value) {
            $productItem = [];
            $productItem['name'] = $value['product']->getName();
            $productItem['price'] = $value['product']->getPrice();
            $productItem['qte'] = $value['quantity'];
            $products[] = $productItem;
        }


        $session = Session::create([
            'line_items' => [
                array_map(fn(array $product) => [
                    'quantity' => $product['qte'],
                    'price_data' => [
                        'currency' => 'xaf',
                        'product_data' => [
                            'name' => $product['name']
                        ],
                        'unit_amount' => $product['price']
                    ],
                ], $products)
            ], // Les produits à payer
            'mode' => 'payment', //mode paiement
            'cancel_url' => 'http://127.0.0.1:8000/pay/cancel', // lien de redirection en cas d annulation de paiement
            'success_url' => 'http://127.0.0.1:8000/pay/success', // lien de redirection en cas de success de paiement
            'billing_address_collection' => 'required',

            'payment_intent_data' => [
                'metadata' => [
                'commandeId'=>$commandeId
            ]
            ]
        ]);

        $this->redirectUrl = $session->url;
    }

    public function getStripeRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
