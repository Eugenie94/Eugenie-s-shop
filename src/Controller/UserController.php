<?php

namespace App\Controller;

use App\Entity\User;       
use App\Entity\ContenuPanier;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\PanierRepository;
use App\Repository\ContenuPanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/compte', name: 'user_index')]
    public function index(UserRepository $userRepository, PanierRepository $panierRepository): Response
    {
        $user = $this->getUser();
        $commandes = $panierRepository->findBy(['utilisateur' => $user, 'etat' => true]);
        return $this->render('user/index.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/commande/{id}', name: 'user_commande', methods: ['GET', 'POST'])]
    public function userCommande(Request $request, PanierRepository $panierRepository, ContenuPanierRepository $contenuPanierRepository, EntityManagerInterface $entityManager): Response
    {
        // Id de la commande dans la route
        $idCommande = $request->attributes->get('id');

        // Récuperation du panier
        $panier = $panierRepository->findOneBy(['id' => $idCommande]);

        // Récupération des produits dans le panier
        $contenuPanier = $contenuPanierRepository->findBy(['panier' => $panier]);
        return $this->render('user/commande.html.twig', [
            'idCommande' => $idCommande,
            'contenu_panier' => $contenuPanier,
            'panier' => $panier
        ]);
    }
}
