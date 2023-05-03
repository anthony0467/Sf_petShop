<?php

namespace App\Controller;

use doctrine;
use App\Entity\User;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
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

    #[Route('/home/add', name: 'add_produit')] // ajouter un produit
    #[Route('/home/{id}/edit', name: 'edit_produit')] // modifier un produit
    public function add(ManagerRegistry $doctrine, Produit $produit = null,  Request $request): Response{
        
         $user = $this->getUser(); // récupérer objet user 
     
    
        

        if(!$produit){ // edit
            $produit = new Produit();
        } //add


        $form = $this->createForm(ProduitType::class, $produit,  []);
        $form->handleRequest($request); // analyse the request

        if($form->isSubmitted() && $form->isValid()){ // valid respecter les contraintes
            if($produit->getDisponible() < 1){
                $this->addFlash('error', 'Le champ "disponible" doit être supérieur ou égal à 1.');
            }



            $produit = $form->getData();

            $produit->setUser($user); // install mon user
            $now = new \DateTime(); // objet date
            $produit->setDateCreationProduit($now); // installe ma date

            $entityManager = $doctrine->getManager(); // on récupère les ressources
            $entityManager->persist($produit); // on enregistre la ressource
            $entityManager->flush(); // on envoie la ressource insert into

            return $this->redirectToRoute('app_produit');
        }

        return $this->render('produit/add.html.twig', [
            'formAddProduit' => $form->createView(), // généré le visuel du form
            "edit" => $produit->getId(),
           
        ]);

    }
}
