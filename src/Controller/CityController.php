<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/editor/city')]
class CityController extends AbstractController
{
    #[Route('/', name: 'app_city_index', methods: ['GET'])]
    public function index(CityRepository $cityRepository, CategoryRepository $categoryRepository): Response
    {

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil active'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];

        return $this->render('city/index.html.twig', [
            'cities' => $cityRepository->findAll(),
            'menuItems' => $menuItems,
            'categories'=> $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_city_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {


        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil active'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];


        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();

            return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('city/new.html.twig', [
            'menuItems' => $menuItems,
            'categories'=> $categoryRepository->findAll(),
            'city' => $city,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_city_show', methods: ['GET'])]
    public function show(City $city, CategoryRepository $categoryRepository): Response
    {


        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil active'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];

        return $this->render('city/show.html.twig', [
            'menuItems' => $menuItems,
            'categories'=> $categoryRepository->findAll(),
            'city' => $city,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_city_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, City $city, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {


        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil active'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];


        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('city/edit.html.twig', [
            'menuItems' => $menuItems,
            'categories'=> $categoryRepository->findAll(),
            'city' => $city,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_city_delete', methods: ['POST'])]
    public function delete(Request $request, City $city, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$city->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($city);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
    }
}
