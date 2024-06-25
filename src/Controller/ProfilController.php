<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    #[Route('/user/profil', name: 'app_profil')]
    public function index(): Response
    {
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];
       
        $user = $this->getUser();

        return $this->render('profil/index.html.twig', [
            'menuItems' => $menuItems,
            'user'=>$user
        ]);
    }
}
