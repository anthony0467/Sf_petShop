<?php

namespace App\Controller;


use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }

    #[Route('/produit/show/{id}', name: 'show_produit')] // vue detaillé du produit
    public function show(ManagerRegistry $doctrine,  Produit $produit = null,): Response
    {
        $user = $this->getUser();
        $vendeur = $produit->getUser();
        
        if($produit){
            
           // $message->setExpediteur($user);
            //$message->setDestinataire($vendeur);

         return $this->render('produit/show.html.twig', [
             'controller_name' => 'HomeController',
             'produit' => $produit,
             
             
             
         ]);
        }else{
            return $this->redirectToRoute('app_home');
        }

    }
}
