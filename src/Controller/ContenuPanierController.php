<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Form\ContenuPanierType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/contenu/panier')]
class ContenuPanierController extends AbstractController
{
    #[Route('/', name: 'contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository, PanierRepository $panierRepository, TranslatorInterface $t): Response
    {
        $user = $this->getUser();
        $panier = $panierRepository->findOneBy(['utilisateur' => $user, 'etat' => false]);
        $contenuPanier = $contenuPanierRepository->findOneBy(['panier' => $panier]);
        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanier
        ]);
    }

    #[Route('/new', name: 'contenu_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $t): Response
    {
        $contenuPanier = new ContenuPanier();
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contenuPanier);
            $entityManager->flush();

            return $this->redirectToRoute('contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contenu_panier/new.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'contenu_panier_show', methods: ['GET'])]
    public function show(ContenuPanier $contenuPanier, TranslatorInterface $t): Response
    {
        return $this->render('contenu_panier/show.html.twig', [
            'contenu_panier' => $contenuPanier,
        ]);
    }

    #[Route('/{id}/edit', name: 'contenu_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContenuPanier $contenuPanier, EntityManagerInterface $entityManager, TranslatorInterface $t): Response
    {
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contenu_panier/edit.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form->createView()
        ]);
    }

    #[Route('delete/{id}', name: 'contenu_panier_delete', methods: ['GET'])]
    public function delete(Request $request, ContenuPanier $contenuPanier, EntityManagerInterface $entityManager, TranslatorInterface $t): Response
    {
            $entityManager->remove($contenuPanier);
            $entityManager->flush();

        return $this->redirectToRoute('panier_achat', [], Response::HTTP_SEE_OTHER);
    }
}
