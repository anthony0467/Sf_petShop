<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Categorie::class)->findBy([], []);
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
            'categories' => $categories,
        ]);
    }

    #[Route('/categorie/show/{id}', name: 'show_categorie')] // afficher prodtuis par categorie
    public function show(ManagerRegistry $doctrine, Categorie $categorie = null): Response
    {
        $categories = $doctrine->getRepository(Categorie::class)->findBy([], []);
        if($categorie){
            $produits = $categorie->getProduits(); // récupère les produits
            return $this->render('categorie/show.html.twig', [
                'categorie' => $categorie,
                'produits' => $produits,
                'categories' => $categories
            ]);
        }else{
            return $this->redirectToRoute('app_home');
        }
       
    }
}
