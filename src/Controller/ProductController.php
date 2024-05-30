<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\AddProductHistoryType;
use App\Form\ProductType;
use App\Form\ProductUpdateType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/editor/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, Request $request, PaginatorInterface $paginator): Response
    {


        $data = $productRepository->findAll();
        $products = $paginator->paginate(
            $data,  // Page a paginer
            $request->query->getInt('page', 1), // Le numero de la page par defaut est 1
            5 // Max par page
        );
        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            if ($image) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('brochures_directory'),
                        $newFileName
                    );
                } catch (FileException $exception) {
                    //throw $th;
                }
                $product->setImage($newFileName);
            }
            $entityManager->persist($product);
            $entityManager->flush();

            $stockHistory = new AddProductHistory();
            $stockHistory->setQte($product->getStock());
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($stockHistory);
            $entityManager->flush();

            $this->addFlash('success', 'Votre produits a été ajouté');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductUpdateType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $image = $form->get('image')->getData();

            if ($image) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalName);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('brochures_directory'),
                        $newFileName
                    );
                } catch (FileException $exception) {
                    //throw $th;
                }
                $product->setImage($newFileName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre produits a été modifié');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('danger', 'Votre produits a été supprimé');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/add/product/{id}', name: 'app_product_stock_add', methods: ['POST', 'GET'])]
    public function addStock($id, EntityManagerInterface $entityManager, Request $request, ProductRepository $productRepository): Response
    {
        $addStock = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryType::class, $addStock);
        $form->handleRequest($request);

        $product = $productRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($addStock->getQte() > 0) {
                $newQte = $product->getStock() +  $addStock->getQte();

                $product->setStock($newQte);

                $addStock->setCreatedAt(new \DateTimeImmutable());
                $addStock->setProduct($product);
                $entityManager->persist($addStock);
                $entityManager->flush();

                $this->addFlash('success', 'le stock de votre produit à été modifier');

                return $this->redirectToRoute('app_product_index');
            } else {
                $this->addFlash('danger', 'le stock ne doit pas être inférieur ou égale à 0');
                return $this->redirectToRoute('app_product_stock_add', [
                    'id' => $product->getId()
                ]);
            }
        }

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('product/addStock.html.twig', [
            'form' => $form->createView(),
            'menuItems' => $menuItems,
            'product' => $product
        ]);
    }

    #[Route('/add/product/{id}/stock/history', name: 'app_product_stock_add_history', methods: ['GET'])]
    public function productAddHistory($id, ProductRepository $productRepository, AddProductHistoryRepository $addProductHistoryRepository): Response
    {
        $product = $productRepository->find($id);
        $productAddedHistory = $addProductHistoryRepository->findBy(['product' => $product], ['id' => 'DESC']);

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Galerie_de_Meubles', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        return $this->render('product/addedStockHistoryShow.html.twig',[
            'productsAdded'=>$productAddedHistory,
            'menuItems' => $menuItems,
        ]);
    }
}
