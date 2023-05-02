<?php

namespace App\Controller;

use doctrine;
use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $produits = $doctrine->getRepository(Produit::class)->findBy([], ["dateCreationProduit"=> "DESC"], 5); // uniquement les 5 derniers articles ajoutés

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'produits' => $produits,
        ]);
    }
}
