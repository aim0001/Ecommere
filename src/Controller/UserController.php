<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $data = $userRepository->findAll();
        $users =  $paginator->paginate(
            $data,  // Page a paginer
            $request->query->getInt('page', 1), // Le numero de la page par defaut est 1
            10 // Max par page
        );

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'menuItems' => $menuItems,
            
        ]);
    }

    #[Route('/{id}/to/editor', name: 'app_user_to_editor')]
    public function changeRole(EntityManagerInterface $entityManager,User $user):Response
    {
        $user->setRoles(["ROLE_EDITOR", "ROLE_USER"]);
        $entityManager->flush();

        $this->addFlash('success', 'Le role editeur a été ajouté à votre utilisateur');

        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/{id}/remove/editor/role', name: 'app_user_remove_editor_role')]
    public function editorRoleRemove(EntityManagerInterface $entityManager,User $user):Response
    {
        $user->setRoles([]);
        $entityManager->flush();

        $this->addFlash('danger', 'Le role editeur a été retiré à votre utilisateur');

        return $this->redirectToRoute('app_user_index');
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {

        $menuItems=[
            ['label'=>'Accueil', 'route'=>'menu_Accueil', 'class'=> 'menu_Accueil'],
            ['label'=>'Galerie_de_Meubles', 'route'=>'menu_Galerie', 'class'=> 'menu_Galerie'],
            ['label'=>'Boutique', 'route'=>'menu_Boutique', 'class'=> 'menu_Boutique']
        ];

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'menuItems' => $menuItems,
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('danger', 'votre utilisateur à été supprimé');

        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
