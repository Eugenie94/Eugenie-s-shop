<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierContentRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
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

    #[Route('/checkout', name: 'panier_checkout', methods: ['GET', 'POST'])]
    public function show(Request $request, PanierRepository $panierRepository, PanierContentRepository $panierContentRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupération du panier en fonction de l'utilisateur
        $user = $this->getUser();
        $panier = $panierRepository->findOneBy(['user' => $user, 'state' => false]);

        // Récupération des produits dans le panier
        $panierContent = $panierContentRepository->findBy(['panier' => $panier]);

        // Paramètre buy défini sur false par défaut pour afficher le panier dans la vie
        $buy = false;

        if ($request->isMethod('POST')) {
            $panier->setState(true);
            $panier->setBuyAt(new \DateTime());
            $entityManager->persist($panier);
            $entityManager->flush();
            $this->addFlash('success', 'Order confirmed! Thank you for buying our amazing products.');

            // On passe le paramètre à true pour ne pas afficher le panier uniquement le btn de retour au catalogue et le message de remerciement
            $buy = true;
        }

        return $this->render('panier/show.html.twig', [
            'panier_content' => $panierContent,
            'buy' => $buy,
        ]);
    }

    #[Route('/{id}/edit', name: 'panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
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
    public function delete(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $panier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($panier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('panier_index', [], Response::HTTP_SEE_OTHER);
    }
}