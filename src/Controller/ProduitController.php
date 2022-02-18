<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProduitController extends AbstractController
{
    // #[Route('/produit', name: 'produit')]
    // public function index(ManagerRegistry $doctrine, Request $request, TranslatorInterface $translator): Response
    // {
    //     $entityManager = $doctrine->getManager();

    //     $produit = new Produit();

    //     // Objet vide pour le formulaire
    //     $form = $this->createForm(ProduitType::class, $produit);

    //     // Détecte la requete HTTP
    //     $form->handleRequest($request);

    //         // Si le formulaire a été soumis et qu'il est valide
    //         if ($form->isSubmitted() && $form->isValid()){

    //             $photo = $form->get('photo')->getData();

    //             if ($photo) {
    //                 $newFilename = uniqid().'.'.$photo->guessExtension();

    //                 try {
    //                     $photo->move(
    //                         $this->getParameter('upload_directory'),
    //                         $newFilename
    //                     );
    //                 } catch (FileException $e) {
    //                     $this->addFlash('danger', "Impossible d'uploader l'image");
    //                     return $this->redirectToRoute('produit');
    //                 }

    //                 $produit->setPhoto($newFilename);
    //             }

    //             // Equivalent prepare PDO
    //             $entityManager->persist($produit);

    //             // Equivalent execute PDO
    //             $entityManager->flush();

    //             $this->addFlash('success', $translator->trans('produit.added'));
    //         }

    //         $produits = $entityManager->getRepository(Produit::class)->findAll();


    //     return $this->render('produit/index.html.twig', [
    //         'produits' => $produits,
    //         'ajout' => $form->createView()
    //     ]);
    // }

    // #[Route('/', name: 'produit_index', methods: ['GET'])]
    // public function index(ProduitRepository $produitRepository): Response
    // {

    //     $produits = $produitRepository->findAllByEtat(true);


    //     return $this->render('produit/index.html.twig', [
    //         'produits' => $produitRepository->findAll(),
    //     ]);
    // }


    #[Route('/produit/{id}', name:'produit_show')]
    public function show(Produit $produit = null, ManagerRegistry $doctrine, Request $request){

        if($produit == null){
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('produit');
        }

        // Création du formulaire d'édition avec un objet qui contient des données
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour');
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'edit' => $form->createView()
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

            // if($produit->getEtat() == true){
            //     $produit->setParution(new \DateTime());
            // }

            $entityManager->persist($produit);

            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié');

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/produit/delete/{id}', name:'produit_delete')]
    public function delete(Produit $p = null, ManagerRegistry $doctrine){
        if($p == null){
            $this->addFlash('danger', 'Produit introuvable');
        }
        else{
            $em = $doctrine->getManager();
            $em->remove($p);
            $em->flush();
            $this->addFlash('danger', 'Produit supprimé');
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }

}
