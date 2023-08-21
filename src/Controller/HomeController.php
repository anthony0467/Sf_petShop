<?php

namespace App\Controller;

use doctrine;
use App\Entity\User;
use App\Entity\Offre;
use App\Entity\Images;
use App\Entity\Slider;
use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\Evenement;
use App\Form\ProduitType;
use App\Form\EtatAnnonceType;
use App\Form\SearchProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('', name: 'app_home')]
    public function index(ManagerRegistry $doctrine, ProduitRepository $Pr, Request $request, PaginatorInterface $paginator): Response
    {
        $produitSearch = null; //  recherche produits
        $slider = $doctrine->getRepository(Slider::class)->findBy([], ["id" => "DESC"]);
        $produits = $doctrine->getRepository(Produit::class)->findBy([], ["dateCreationProduit" => "DESC"], 4); // uniquement les 4 derniers articles ajoutés
        $evenements = $doctrine->getRepository(Evenement::class)->findBy([], ["dateEvenement" => "DESC"], 2); // uniquement les 2 derniers évenements

        //pagination tous les produits 
        $pagination = $paginator->paginate(
            $Pr->allProduits(),
            $request->query->get('page', 1),
            15
        );



        $form = $this->createForm(SearchProduitType::class, null, [
            'attr' => ['id' => 'search-form'], // Ajouter l'ID "formulaire" au formulaire
        ]);

        $search = $form->handleRequest($request);
        //dd($search);
        if ($form->isSubmitted() && $form->isValid()) {
            //on recherche les produits correspondant au mots clef

            $produitSearch = $Pr->search($search->get('mots')->getData(), $search->get('categorie')->getData());
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'produits' => $produits,
            'produitSearch' => $produitSearch,
            'paginations' => $pagination,
            'evenements' => $evenements,
            'slide' => $slider,
            'form' => $form->createView()
        ]);
    }

    #[Route('/search', name: 'search_results')] // route pour l'ajax recherche de produit
    public function searchResult(ProduitRepository $Pr, Request $request): JsonResponse
    {
        // Récupérer les paramètres de recherche envoyés via AJAX
        $mots = $request->query->get('mots');
        $categorie = $request->query->get('categorie');
        $produitSearch = $Pr->search($mots, $categorie);


        return $this->json([
            'result' => $this->renderView('home/_search_articles.html.twig', ['produitSearch' => $produitSearch])
        ]);
    }


    #[Route('/pagination', name: 'app_pagination')]
    public function pagination(ProduitRepository $Pr, Request $request, PaginatorInterface $paginator): JsonResponse
    {
        // Récupérer la page demandée depuis la requête
        $page = $request->query->getInt('page', 1);

        // Nombre d'articles à afficher par page
        $limit = 15;

        // Effectuer la recherche en utilisant les paramètres
        $pagination = $paginator->paginate(
            $Pr->allProduits(),
            $page,
            $limit
        );

        // Renvoyer les résultats paginés au format HTML
        return $this->json([
            'resultProduct' => $this->renderView('home/_all_articles.html.twig', ['paginations' => $pagination])
        ]);;
    }

    #[Route('/home/add', name: 'add_produit')] // ajouter un produit
    #[Route('/home/{id}/edit', name: 'edit_produit')] // modifier un produit
    public function add(ManagerRegistry $doctrine, Produit $produit = null,  Request $request): Response
    {


        $user = $this->getUser(); // récupérer objet user 

        if (!$produit) { // edit
            $produit = new Produit();
        } //add


        $form = $this->createForm(ProduitType::class, $produit,  []);
        $form->handleRequest($request); // analyse the request

        if ($form->isSubmitted() && $form->isValid()) { // valid respecter les contraintes
            // on recupere les images transmisse
            $images = $form->get('images')->getData();

            // on boucle sur les tableaux
            foreach ($images as $image) {
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
            $produit->setEtat(false);

            $entityManager = $doctrine->getManager(); // on récupère les ressources
            $entityManager->persist($produit); // on enregistre la ressource
            $entityManager->flush(); // on envoie la ressource insert into

            return $this->redirectToRoute('show_home');
        }

        return $this->render('produit/add.html.twig', [
            'formAddProduit' => $form->createView(), // généré le visuel du form
            "edit" => $produit->getId(),
            "produits" => $produit,


        ]);
    }


    #[Route('/home/delete/{id}', name: 'delete_produit')] // supprimer le produit
    public function delete(ManagerRegistry $doctrine, Produit $produit = null): Response
    {
        if ($produit) {
            $entityManager = $doctrine->getManager();

            // Supprimer les images associées
            $images = $produit->getImages();
            foreach ($images as $image) {

                $imagePath = $this->getParameter('images_directory') . '/' . $image->getNomImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
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
    public function deleteImage(ManagerRegistry $doctrine, Images $image, Request $request)
    {
        $entityManager = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

        // on recupere le nom de l'image
        $nom = $image->getNomImage();
        // on supprime le fichier
        unlink($this->getParameter('images_directory') . '/' . $nom);
        // on supprime l'entrer de la base
        $entityManager->remove($image);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/home/show', name: 'show_home')] // vue profil de l'utilisateur
    public function show(ManagerRegistry $doctrine, ProduitRepository $Pr): Response
    {
        $user = $this->getUser();
        $produits = $doctrine->getRepository(Produit::class)->findBy(['user' => $user], ["dateCreationProduit" => "DESC"]); // uniquement les 5 derniers articles ajoutés
        $categories = $doctrine->getRepository(Categorie::class)->findBy([], []);
        $offres = $doctrine->getRepository(Offre::class)->findBy(['Users' => $user], ['date' => 'DESC']); // uniquement les 5 derniers articles
        $offresObtenu = $doctrine->getRepository(Offre::class)->findBy([], []);

        return $this->render('home/profil.html.twig', [
            'controller_name' => 'HomeController',
            'produits' => $produits,
            'categories' => $categories,
            'offres' => $offres,
            'offresObtenu' => $offresObtenu



        ]);

        //return $this->render('home/index.html.twig', []);
    }

    #[Route('/home/admin', name: 'show_admin')] // vue profil de l'utilisateur
    public function admin(ManagerRegistry $doctrine, ProduitRepository $pi): Response
    {
        //$produits = $doctrine->getRepository(Produit::class)->findBy([], ["dateCreationProduit"=> "DESC"]); 

        $slider = $doctrine->getRepository(Slider::class)->findBy([]);
        $produitInactif = $pi->annonceInactif(); // requete dql
        $produitActif = $pi->annonceActif(); // requete dql

        return $this->render('home/admin.html.twig', [
            'controller_name' => 'HomeController',
            'produits' => $produitInactif,
            'produitsActif' => $produitActif,
            'slider' => $slider,



        ]);
    }

    #[Route('/home/admin/publish/{id}', name: 'publish_annonce')] // publier annonce
    //#[IsGranted('ROLE_ADMIN')]
    public function publishAnnonce(ManagerRegistry $doctrine, Produit $produit)
    {


        if ($produit->isEtat()) {
            $produit->setEtat(false);
        } else {
            $produit->setEtat(true);
        }

        $em = $doctrine->getManager();
        $em->flush();

        // Retourner une réponse JSON indiquant le nouvel état de l'annonce
        return new JsonResponse(['etat' => $produit->isEtat()]);
    }


    #[Route('/user/show/{id}', name: 'show_user')] // afficher autre utilisateur
    public function showUser(ManagerRegistry $doctrine, User $user = null): Response
    {
        $produits = $doctrine->getRepository(Produit::class)->findBy([], ["dateCreationProduit" => "DESC"]);

        if ($user) {

            $nbProduitsActifs = 0; // compter le nombre d'annonce avec etat actif
            foreach ($user->getProduits() as $produit) {
                if ($produit->isEtat()) {
                    $nbProduitsActifs++;
                }
            }

            return $this->render('home/profilOtherUser.html.twig', [
                'userProfil' => $user,
                'produits' => $produits,
                'nbProduitsActifs' => $nbProduitsActifs,
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
    #[Route('/error', name: 'error_page')] // page 404
    public function errorRedirection(): Response
    {
        return $this->render('errorPage/404.html.twig', []);
    }
}
