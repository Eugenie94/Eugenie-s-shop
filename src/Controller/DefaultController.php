<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProduitRepository $repo, TranslatorInterface $t): Response
    {
        $produits = $repo->findAll();

        return $this->render('default/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
