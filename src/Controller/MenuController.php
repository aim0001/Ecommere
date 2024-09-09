<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    #[Route('/', name: 'menu_Accueil')]
    public function index(Request $request, MailerInterface $mailer, SessionInterface $session): Response
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Entrez votre email',
                    'class' => 'form-control'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Tapez votre message...',
                    'class' => 'form-control',
                    'rows' => 4  // Nombre de lignes par défaut
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyez votre message',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Envoyer l'email
            $email = (new Email())
                ->from($data['email'])
                ->to('votre_email@example.com')  // Remplacez par votre adresse email
                ->subject('Nouveau message de ' . $data['name'])
                ->text($data['message']);

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a été envoyé avec succès!');

            return $this->redirectToRoute('menu_contact');
        }

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Aperçus', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];


        return $this->render('menu/index.html.twig', [
            'menuItems' => $menuItems,
            'form' => $form->createView(),
            // 'categories'=> $categoryRepository->findAll(),
        ]);
    }


    #[Route('/galerie', name: 'menu_Galerie')]
    public function gallerie(CategoryRepository $categoryRepository, ProductRepository $productRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Aperçus', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie active'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];

        $data = $productRepository->findBy([], ['id' => "DESC"]);
        $products =  $paginator->paginate(
            $data,  // Page a paginer
            $request->query->getInt('page', 1), // Le numero de la page par defaut est 1
            20 // Max par page
        );


        return $this->render('menu/galerie.html.twig', [
            'menuItems' => $menuItems,
            'categories' => $categoryRepository->findAll(),
            'products' => $products,
        ]);
    }


    #[Route('/boutique', name: 'menu_Boutique', methods: ['GET'])]
    public function boutique(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator, Session $session): Response
    {

        // $search = $productRepository->searchEngine();
        $data = $productRepository->findBy([], ['id' => "DESC"]);
        $products =  $paginator->paginate(
            $data,  // Page a paginer
            $request->query->getInt('page', 1), // Le numero de la page par defaut est 1
            9 // Max par page
        );

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Aperçus', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique active']
        ];


        $cart = $session->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        // $cartPagination = $paginator->paginate(
        //     $cartWithData,
        //     $request->query->getInt('cartPage',1),
        //     4
        // );

        $total = array_sum(array_map(function ($item) {
            return $item['product']->getPrice() * $item['quantity'];
        }, $cartWithData));


        return $this->render('menu/boutique.html.twig', [
            // 'itemPaginate' => $cartPagination,
            'items' => $cartWithData,
            'total' => $total,
            'menuItems' => $menuItems,
            'products' => $products,
            'categories' => $categoryRepository->findAll()
        ]);
    }

    #[Route('/home/product/{id}/show', name: 'app_home_product_show', methods: ['GET'])]
    public function show(Product $product, ProductRepository $productRepository, CategoryRepository $categoryRepository, Session $session): Response
    {

        $lastProducts = $productRepository->findBy([], ['id' => 'DESC'], 5);

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Aperçus', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique active']
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

        return $this->render('menu/show.html.twig', [
            'items' => $cartWithData,
            'total' => $total,
            'menuItems' => $menuItems,
            'product' => $product,
            'categories' => $categoryRepository->findAll(),
            'products' => $lastProducts
        ]);
    }


    #[Route('/home/product/subCategory/{id}/filter', name: 'app_home_product_filter', methods: ['GET'])]
    public function filter($id, SubCategoryRepository $subCategoryRepository, CategoryRepository $categoryRepository, ProductRepository $productRepository, Session $session): Response
    {

        $products = $subCategoryRepository->find($id)->getProducts();
        $subCategory = $subCategoryRepository->find($id);

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil'],
            ['label' => 'Aperçus', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique active']
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

        return $this->render('menu/filter.html.twig', [
            'items' => $cartWithData,
            'total' => $total,
            'menuItems' => $menuItems,
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
            'subCategory' => $subCategory,
        ]);
    }

    #[Route('/contact', name: 'menu_contact')]
    public function Contact(Request $request, MailerInterface $mailer, SessionInterface $session): Response
    {

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Entrez votre email',
                    'class' => 'form-control'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Tapez votre message...',
                    'class' => 'form-control',
                    'rows' => 4  // Nombre de lignes par défaut
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyez votre message',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            
            // Envoyer l'email

            $email = (new Email())
                ->from($data['email'])
                ->to('votre_email@example.com')  // Remplacez par votre adresse email
                ->subject('Nouveau message de ' . $data['name'])
                ->html($data['message']);

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a été envoyé avec succès!');

            return $this->redirectToRoute('menu_contact');
        }

        $menuItems = [
            ['label' => 'Accueil', 'route' => 'menu_Accueil', 'class' => 'menu_Accueil active'],
            ['label' => 'Aperçus', 'route' => 'menu_Galerie', 'class' => 'menu_Galerie'],
            ['label' => 'Boutique', 'route' => 'menu_Boutique', 'class' => 'menu_Boutique']
        ];


        return $this->render('menu/contact.html.twig', [
            'menuItems' => $menuItems,
            'form' => $form->createView(),
            // 'categories'=> $categoryRepository->findAll(),
        ]);
    }
}
