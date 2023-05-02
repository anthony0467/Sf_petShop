<?php

namespace App\Controller;

use doctrine;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

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
    public function add(ManagerRegistry $doctrine, Produit $produit = null, Request $request): Response{

        $user = $this->getUser();

        if(!$produit){ // edit
            $produit = new Produit();
        } //add

        $form = $this->createForm(ProduitType::class, $produit,  [
            'user' => $user,
        ]);
        $form->handleRequest($request); // analyse the request

        if($form->isSubmitted() && $form->isValid()){ // valid respecter les contraintes
            $produit = $form->getData();
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
