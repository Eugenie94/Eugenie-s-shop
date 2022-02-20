<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Produit;
use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Form\ProduitType;
use App\Form\ContenuPanierType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProduitController extends AbstractController
{
    #[Route('/', name: 'produit_index')]
    public function index(ManagerRegistry $doctrine, Request $request, TranslatorInterface $translator): Response
    {
        $entityManager = $doctrine->getManager();

        $produit = new Produit();

        // Objet vide pour le formulaire
        $form = $this->createForm(ProduitType::class, $produit);

        // Détecte la requete HTTP
        $form->handleRequest($request);

            // Si le formulaire a été soumis et qu'il est valide
            if ($form->isSubmitted() && $form->isValid()){

                $photo = $form->get('photo')->getData();

                if ($photo) {
                    $newFilename = uniqid().'.'.$photo->guessExtension();

                    try {
                        $photo->move(
                            $this->getParameter('upload_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('danger', "Impossible d'uploader l'image");
                        return $this->redirectToRoute('produit');
                    }

                    $produit->setPhoto($newFilename);
                }

                // Equivalent prepare PDO
                $entityManager->persist($produit);

                // Equivalent execute PDO
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('produit.added'));
            }

            $produits = $entityManager->getRepository(Produit::class)->findAll();


        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'form' => $form->createView()
        ]);
    }

    #[Route('/produit/{id}', name:'produit_show')]
    public function show(Produit $produit = null, EntityManagerInterface $entityManager, Request $request, PanierRepository $panierRepository, ContenuPanierRepository $contenuPanierRepository){

        if($produit == null){
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('produit');
        }

        // Récupération du contenu du panier
        $contenuPanier = new ContenuPanier();

        // Création du formulaire d'ajout du produit dans le panier
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération du panier non commandé de l'utilisateur
            $user = $this->getUser();
            $userPanier = $panierRepository->findOneBy(['utilisateur' => $user, 'etat' => false]);

            // Si l'utilisateur n'a pas de panier non commandé alors j'effectue la création de son panier
            if ($userPanier === null) {
                $userPanier = new Panier();
                $userPanier->setUtilisateur($user);
                $userPanier->setEtat(false);
                $entityManager->persist($userPanier);
                $entityManager->flush();
            }

            // Récupération du produit si il est déjà dans le panier utilisateur
            $produitContenuPanier = $contenuPanierRepository->findOneBy(['panier' => $userPanier, 'produit' => $produit]);

            // Si le produit n'est pas dans ce panier alors on l'ajoute sinon on met à jour sa quantité
            if ($produitContenuPanier === null) {
                $contenuPanier->setDate(new \DateTime());
                $contenuPanier->setProduit($produit);
                $contenuPanier->setPanier($userPanier);
                $entityManager->persist($contenuPanier);
                $entityManager->flush();
            } else {
                $produitContenuPanier->setQuantite($produitContenuPanier->getQuantite() + $contenuPanier->getQuantite());
                $entityManager->persist($produitContenuPanier);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Votre produit a été ajouté au panier !');

        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'form' => $form->createView()
        ]);
    }


    #[Route('/new', name: 'produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        
            // Si le formulaire a été soumis et qu'il est valide
            if ($form->isSubmitted() && $form->isValid()){

                $photo = $form->get('photo')->getData();

                if ($photo) {
                    $newFilename = uniqid().'.'.$photo->guessExtension();

                    try {
                        $photo->move(
                            $this->getParameter('upload_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('danger', "Impossible d'uploader l'image");
                        return $this->redirectToRoute('produit');
                    }

                    $produit->setPhoto($newFilename);
                }

                $entityManager->persist($produit);
                $entityManager->flush();
    
                $this->addFlash('success', $translator->trans('Produit ajouté'));
            }
            
            $produits = $entityManager->getRepository(Produit::class)->findAll();

            return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView()
        ]);
        
    }

    #[Route('/edit/{id}', name: 'produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($produit);

            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié');

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView()
        ]);
    }

    #[Route('/produit/delete/{id}', name:'produit_delete')]
    public function delete(Produit $p = null, ManagerRegistry $doctrine){
        
        if($p == null){
            $this->addFlash('danger', 'Produit introuvable');
        }
        #TODO if produit in panier before delete
        else{
            $em = $doctrine->getManager();
            $em->remove($p);
            $em->flush();
            $this->addFlash('danger', 'Produit supprimé');
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }

}
