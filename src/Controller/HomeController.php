<?php

namespace App\Controller;

use doctrine;
use App\Entity\User;
use App\Entity\Images;
use App\Entity\Produit;
use App\Entity\Categorie;
use App\Form\ProduitType;
use App\Form\EtatAnnonceType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $produits = $doctrine->getRepository(Produit::class)->findBy([], ["dateCreationProduit"=> "DESC"], 5); // uniquement les 5 derniers articles ajoutés
        $categories = $doctrine->getRepository(Categorie::class)->findBy([], []);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'produits' => $produits,
            'categories' => $categories,
        ]);
    }

    

    #[Route('/home/add', name: 'add_produit')] // ajouter un produit
    #[Route('/home/{id}/edit', name: 'edit_produit')] // modifier un produit
    public function add(ManagerRegistry $doctrine, Produit $produit = null,  Request $request): Response{

        $categories = $doctrine->getRepository(Categorie::class)->findBy([], []);
        
         $user = $this->getUser(); // récupérer objet user 
     
        if(!$produit){ // edit
            $produit = new Produit();
        } //add


        $form = $this->createForm(ProduitType::class, $produit,  []);
        $form->handleRequest($request); // analyse the request

        if($form->isSubmitted() && $form->isValid()){ // valid respecter les contraintes
           // on recupere les images transmisse
           $images = $form->get('images')->getData();

           // on boucle sur les tableaux
           foreach($images as $image){
            // on genere un nouveau nom de fichier
            $fichier = md5(uniqid()) . '.' . $image->guessExtension();

            //on copie le fichier dans le dossier uploads
            $image->move(
                $this->getParameter('images_directory'),
                $fichier
            );

            //on stocke l'image dans la base de données
            $img = new Images();
            $img->setNomImage($fichier);
            $produit->addImage($img);

           }

            $produit = $form->getData();

            $produit->setUser($user); // install mon user
            $now = new \DateTime(); // objet date
            $produit->setDateCreationProduit($now); // installe ma date

            $entityManager = $doctrine->getManager(); // on récupère les ressources
            $entityManager->persist($produit); // on enregistre la ressource
            $entityManager->flush(); // on envoie la ressource insert into

            return $this->redirectToRoute('show_home');
        }

        return $this->render('produit/add.html.twig', [
            'formAddProduit' => $form->createView(), // généré le visuel du form
            "edit" => $produit->getId(),
            "produits" => $produit,
            "categories" => $categories
           
        ]);

    }


    #[Route('/home/delete/{id}', name: 'delete_produit')] // supprimer la session
    public function delete(ManagerRegistry $doctrine, Produit $produit = null): Response
{
    if ($produit) {
        $entityManager = $doctrine->getManager();

        // Supprimer les images associées
        $images = $produit->getImages();
        foreach ($images as $image) {
            $entityManager->remove($image);
        }

        // Supprimer le produit
        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute('show_home');
    } else {
        return $this->redirectToRoute('show_home');
    }
}



    #[Route('/home/delete/image/{id}', name: 'delete_image')] // supprimer image produit
    public function deleteImage(ManagerRegistry $doctrine, Images $image, Request $request) {
        $entityManager = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

            // on recupere le nom de l'image
            $nom = $image->getNomImage();
            // on supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);
            // on supprime l'entrer de la base
            $entityManager->remove($image);
            $entityManager->flush();

            return $this->redirect($request->headers->get('referer'));
          
    }

    #[Route('/home/show', name: 'show_home')] // vue profil de l'utilisateur
    public function show(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        $produits = $doctrine->getRepository(Produit::class)->findBy(['user' => $user], ["dateCreationProduit"=> "DESC"]); // uniquement les 5 derniers articles ajoutés
        $categories = $doctrine->getRepository(Categorie::class)->findBy([], []);

         return $this->render('home/profil.html.twig', [
             'controller_name' => 'HomeController',
             'produits' => $produits,
             'categories' => $categories,
             
             
         ]);

        return $this->render('home/index.html.twig', []);
    }

    #[Route('/home/admin', name: 'show_admin')] // vue profil de l'utilisateur
    public function admin(ManagerRegistry $doctrine, Request $request): Response
{
    $produits = $doctrine->getRepository(Produit::class)->findBy([], ["dateCreationProduit"=> "DESC"]); 
    $categories = $doctrine->getRepository(Categorie::class)->findBy([], []); // menu categorie
   
    
    $forms = array();
    
    foreach ($produits as $prod) {
        $form = $this->createForm(EtatAnnonceType::class, $prod, );
        $forms[$prod->getId()] = $form->createView();
    }
       
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $etat = $form->getData();
            $etat->setEtat($form->get('etat')->getData());
            $entityManager = $doctrine->getManager();
            $produit = $entityManager->getRepository(Produit::class)->find($prod->getId());
            $produit->isEtat($etat);
           dd($produit);
           // $entityManager->persist($produit);
            $entityManager->flush();
            
            // Afficher un message de confirmation ou rediriger l'utilisateur
            
        }
        //dd($prod);
        // Définir l'état du produit directement dans la boucle
       // $prod->setEtat($prod->isEtat());
    
    

    return $this->render('home/admin.html.twig', [
        'controller_name' => 'HomeController',
        'produits' => $produits,
        'categories' => $categories,
        'formChangeEtat' => $forms,
    ]);
}

}
