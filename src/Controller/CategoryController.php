<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $data = $categoryRepository->findAll();
        $categories =  $paginator->paginate(
            $data,  // Page a paginer
            $request->query->getInt('page', 1), // Le numero de la page par defaut est 1
            5 // Max par page
        );

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success','Votre categorie à été ajouter');

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];


        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success','Votre categorie à été modifier');


            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('danger','Une categorie à été supprimer');

        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
