<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\ContenuPanier;
use App\Form\PanierType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository, TranslatorInterface $t): Response
    {
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $t): Response
    {
        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($panier);
            $entityManager->flush();

            return $this->redirectToRoute('panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('panier/new.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }

    #[Route('/achat', name: 'panier_achat', methods: ['GET', 'POST'])]
    public function show(Request $request, PanierRepository $panierRepository, ContenuPanierRepository $contenuPanierRepository, EntityManagerInterface $entityManager, TranslatorInterface $t): Response
    {
        // Récupération du panier en fonction de l'utilisateur
        $user = $this->getUser();
        $panier = $panierRepository->findOneBy(['utilisateur' => $user, 'etat' => false]);

        // Récupération des produits dans le panier
        $contenuPanier = $contenuPanierRepository->findBy(['panier' => $panier]);

        // False par defaut pour afficher le panier
        $achat = false;

        if ($request->isMethod('POST')) {
            $panier->setDateAchat(new \DateTime());
            $panier->setEtat(true);
            $entityManager->persist($panier);
            $entityManager->flush();
            $this->addFlash('success', $t->trans('PanierController.commandeOk'));

            // On repasse le parametre a true pour ne pas afficher que le panier
            $achat = true;
        }

        return $this->render('panier/show.html.twig', [
            'contenu_panier' => $contenuPanier,
            'achat' => $achat,
        ]);
    }

    #[Route('/{id}/edit', name: 'panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panier $panier, EntityManagerInterface $entityManager, TranslatorInterface $t): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'panier_delete', methods: ['POST'])]
    public function delete(Request $request, Panier $panier, EntityManagerInterface $entityManager, TranslatorInterface $t): Response
    {
        if ($this->isCsrfTokenValid('delete' . $panier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($panier);
            $entityManager->flush();
        }

        $this->addFlash('success', $t->trans('PanierController.prodDeletePanier'));

        return $this->redirectToRoute('panier_index', [], Response::HTTP_SEE_OTHER);
    }
}