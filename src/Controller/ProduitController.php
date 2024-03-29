<?php

namespace App\Controller;


use Stripe\Stripe;
use App\Entity\Avis;
use App\Entity\User;
use App\Entity\Offre;
use App\Entity\Produit;
use App\Form\OffreType;
use App\Form\OrderType;
use App\Entity\Commande;
use App\Entity\Categorie;
use App\Entity\Notification;
use Symfony\Component\Mime\Email;
use App\Repository\AvisRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/produit/show/{slug}', name: 'show_produit')]
    public function show(ManagerRegistry $doctrine, Produit $produit = null, Request $request, PaginatorInterface $paginator, AvisRepository $av): Response
    {
        if ($produit === null) {
            // Si le produit n'existe pas, redirigez l'utilisateur vers une page d'erreur ou une autre page appropriée.
            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();

        // Vérifiez si l'utilisateur est connecté avant d'accéder à $user
        if ($user !== null) {
            $vendeur = $produit->getUser(); // Récupérez le vendeur du produit
            $avis = $av->findBy(['Vendeur' => $vendeur], ["id" => "DESC"]);

            // Pagination de tous les avis
            $paginationAvis = $paginator->paginate(
                $avis,
                $request->query->get('page', 1),
                6
            );

            if ($produit->isIsSelling() == false && $produit->isEtat() == true) {
                return $this->render('produit/show.html.twig', [
                    'controller_name' => 'HomeController',
                    'produit' => $produit,
                    'avis' => $avis,
                    'paginations' => $paginationAvis,
                ]);
            }
        }

        // Redirigez l'utilisateur vers la page d'accueil par défaut
        return $this->redirectToRoute('app_home');
    }

    #[Route('/pagination/produit/show/{slug}', name: 'app_pagination_avis')] // pagination des produits
    public function pagination(ProduitRepository $produitRepository, Request $request, PaginatorInterface $paginator, AvisRepository $av): JsonResponse
    {
        // Récupérer la page demandée depuis la requête
        $page = $request->query->getInt('page', 1);
        // Récupérer le produit en fonction du slug
        $slug = $request->attributes->get('slug');
        $produit = $produitRepository->findOneBy(['slug' => $slug]);
        $vendeur = $produit->getUser(); // Récupérez le vendeur du produit
        $avis = $av->findBy(['Vendeur' => $vendeur], ["id" => "DESC"]);
        // Nombre d'articles à afficher par page
        $limit = 6;

        // Effectuer la recherche en utilisant les paramètres
        $pagination = $paginator->paginate(
            $avis,
            $page,
            $limit
        );

        // Renvoyer les résultats paginés au format HTML
        return $this->json([
            'resultProduct' => $this->renderView('produit/_avis_client.html.twig', [
                'paginations' => $pagination,
                'produit' => $produit, // Passer le produit à la vue partielle
            ])
        ]);
    }





    #[Route('/produit/order/{id}', name: 'show_order')] // vue de la commande
    public function showOrder(ManagerRegistry $doctrine,  Produit $produit = null, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($produit) {

            $commande = new Commande;
            $form = $this->createForm(OrderType::class, $commande,  []);
            $form->handleRequest($request); // analyse the request

            if ($form->isSubmitted() && $form->isValid()) { // valid respecter les contraintes

                $commande = $form->getData();

                //$commande->setUser($user); // install mon user
                $now = new \DateTime(); // objet date
                $commande->setDateCommande($now); // installe ma date
                $commande->setCommander($user);
                $commande->setProduit($produit);
                $commande->setEtat(0);
                // Gérer le message de notification
                if ($produit->getNomProduit()) {
                    $notificationMessage = "Félicitation! Le produit : \"" . $produit->getNomProduit() . "\" a été vendu, merci de procéder rapidement à l'envoi.";
                } else {
                    $notificationMessage = "Félicitation! Un produit a été vendu, merci de procéder rapidement à l'envoi.";
                }
                $commande->setMessage($notificationMessage);
                $entityManager = $doctrine->getManager(); // on récupère les ressources
                $entityManager->persist($commande); // on enregistre la ressource
                $entityManager->flush(); // on envoie la ressource insert into

                return $this->redirectToRoute(
                    'app_test',
                    ['produitId' => $produit->getId()]
                );
            }



            return $this->render('produit/order.html.twig', [
                'controller_name' => 'HomeController',
                'produit' => $produit,
                'formCommande' => $form->createView(),

            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/produit/sent/{id}', name: 'order_sent')] // publier annonce
    //#[IsGranted('ROLE_ADMIN')]
    public function sentProduct(ManagerRegistry $doctrine, Commande $commande, MailerInterface $mailer)
    {


        if ($commande->isIsSent()) {
            $commande->setIsSent(false);
        } else {
            $commande->setIsSent(true);
            $commande->setMessageSent("Votre commande concernant \"" . $commande->getProduit()->getNomProduit() . "\" à bien été envoyé.");

            $clientEmail = $commande->getCommander()->getEmail();
            $produitNom = $commande->getProduit()->getNomProduit();
            $nomVendeur = $commande->getProduit()->getUser()->getPseudo();
            $email = (new Email())
                ->from('admin@worldofpets.com')
                ->to($clientEmail)
                ->subject("WorlfofPets - Votre colis \"" . $produitNom . "\" a bien été envoyé")
                ->text("Le colis $produitNom. a été envoyé par " . $nomVendeur .  ".  L'équipe WorldofPets");

            $mailer->send($email);
        }

        $em = $doctrine->getManager();
        $em->flush();

        return $this->redirectToRoute('show_home');
    }

    #[Route('/produit/received/{id}', name: 'order_received')] // publier annonce
    //#[IsGranted('ROLE_ADMIN')]
    public function receivedProduct(ManagerRegistry $doctrine, Commande $commande, MailerInterface $mailer)
    {


        if ($commande->isIsReceived()) {
            $commande->setIsReceived(false);
        } else {
            $commande->setIsReceived(true);
            //$commande->setMessageSent("Votre commande concernant \"" . $commande->getProduit()->getNomProduit() . "\" à bien été envoyé.");

            $clientEmail = $commande->getProduit()->getUser()->getEmail();
            $produitNom = $commande->getProduit()->getNomProduit();
            $nomVendeur = $commande->getProduit()->getUser()->getPseudo();
            $email = (new Email())
                ->from('admin@worldofpets.com')
                ->to($clientEmail)
                ->subject("WorlfofPets - Colis \"" . $produitNom . "\"  receptionné")
                ->text("Votre colis $produitNom a bien été receptionné par l'acheteur.  L'équipe WorldofPets");

            $mailer->send($email);
        }

        $em = $doctrine->getManager();
        $em->flush();

        return $this->redirectToRoute('show_home');
    }

    #[Route('/offre/{vendorId}/{productId}', name: 'show_offre')] // formulaire offre 
    public function send(Request $request, ManagerRegistry $doctrine, $vendorId, $productId, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $offre = new Offre();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $produit = $doctrine->getRepository(Produit::class)->find($productId);
        $offreRepository = $doctrine->getRepository(Offre::class);

        // Vérifier si l'utilisateur a déjà fait une offre pour ce produit
        $existingOffre = $offreRepository->findOneBy(['Users' => $user, 'produits' => $produit]);
        $offreIsDeleted = $offreRepository->findOneBy([]);
        //dd($existingOffre);

        // Vérifier si une offre a été acceptée pour ce produit
        $offreAcceptee = $offreRepository->findOneBy(['produits' => $produit, 'statut' => Offre::STATUT_ACCEPTEE]);

        if ($offreAcceptee) {
            $this->addFlash('error', 'Une offre a déjà été acceptée pour ce produit.');
            return $this->redirectToRoute('show_home');
        }

        if ($existingOffre && $existingOffre->isIsDeleted() === false) {
            $this->addFlash('error', 'Une offre existe déjà pour ce produit.');
            return $this->redirectToRoute('show_home');
        }


        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offre->setStatut(Offre::STATUT_EN_ATTENTE);
            $offre->setUsers($user);
            $offre->setNotifStatus(false);
            $offre->setProduits($produit);
            $offre->setDate(new \DateTime);
            $em = $doctrine->getManager();
            $em->persist($offre);
            $em->flush();

            // Créer et enregistrer une nouvelle notification pour l'offre en attente
            $notification = new Notification();

            $now = new \DateTime(); // objet date
            $notification->setDate($now);
            $notification->setUser($user);
            $notification->setMessage("Votre offre pour le produit \"" . $produit->getNomProduit() . "\" est en attente.");
            $notification->setMessageDestinataire("Vous avez reçu une offre concernant le produit \"" . $produit->getNomProduit() . "\" ");
            $notification->setOffre($offre);

            $em->persist($notification);
            $em->flush();

            $clientEmail = $offre->getProduits()->getUser()->getEmail();
            $produitNom = $offre->getProduits()->getNomProduit();

            $email = (new Email())
                ->from('admin@worldofpets.com')
                ->to($clientEmail)
                ->subject("WorlfofPets - Vous avez reçu une offre pour \"" . $produit->getNomProduit() . "\"")
                ->text("Vous avez reçu une offre concernant le produit $produitNom. Merci de vous rendre dans votre espace personnel pour accepter ou refuser l'offre. L'équipe WorldofPets");

            $mailer->send($email);

            $this->addFlash("message", "Offre envoyée avec succès", "success");
            return $this->redirectToRoute('show_home');
        }

        return $this->render('produit/offre.html.twig', [
            "form" => $form->createView(),
        ]);
    }




    #[Route('/changer-statut-offre/{id}/{statut}', name: 'changer_statut_offre')] // changer le statut de l'offre 
    public function changerStatutOffre(ManagerRegistry $doctrine, Offre $offre, string $statut, MailerInterface $mailer): Response
    {
        // Vérifier que l'utilisateur connecté est bien le propriétaire de l'offre
        if ($this->getUser() !== $offre->getProduits()->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();

        // Mettre à jour le statut de l'offre en fonction de la valeur passée dans l'URL
        switch ($statut) {
            case 'acceptee':

                Stripe::setApiKey('sk_test_51NibhTEctxRE8ZHzRSOxVx6iKTB7WP0MobKRL4IlWwtpmv7jkZ3ORBaS3zmprfTUVWrg6M4kxBrGdTUmTJikf7Xd00Up0YjBEr');

                // Mettre à jour le prix de base du produit avec le prix de l'offre si l'utilisateur est le propriétaire
                if ($this->getUser() === $offre->getProduits()->getUser()) {
                    $offre->getProduits()->setPrixOffre($offre->getPrix());
                }


                // ... Code pour l'offre acceptée ...
                $offre->setStatut(Offre::STATUT_ACCEPTEE);
                // Créer et enregistrer une nouvelle notification pour l'acceptation de l'offre
                $notificationAcceptee = new Notification();
                $notificationAcceptee->setMessage("Votre offre pour le produit \"" . $offre->getProduits()->getNomProduit() . "\" a été acceptée.");
                $notificationAcceptee->setMessageDestinataire("Vous avez accepté l'offre concernant le produit \"" . $offre->getProduits()->getNomProduit() . "\".");
                $notificationAcceptee->setOffre($offre);
                $notificationAcceptee->setUser($offre->getUsers());

                $now = new \DateTime(); // objet date
                $notificationAcceptee->setDate($now);

                $entityManager->persist($notificationAcceptee);
                $this->addFlash("success", "Offre concernant le produit \"" . $offre->getProduits()->getNomProduit() . "\"  acceptée");
                break;
            case 'refusee':
                // Créer et enregistrer une nouvelle notification pour le refus de l'offre
                $offre->setStatut(Offre::STATUT_REFUSEE);
                $offre->setIsDeleted(true);
                $notificationRefusee = new Notification();
                $notificationRefusee->setMessage("Votre offre pour le produit \"" . $offre->getProduits()->getNomProduit() . "\" a été refusée.");
                $notificationRefusee->setMessageDestinataire("Vous avez refusé l'offre concernant le produit \"" . $offre->getProduits()->getNomProduit() . "\".");
                $notificationRefusee->setOffre($offre);
                $notificationRefusee->setUser($offre->getUsers());
                $now = new \DateTime(); // objet date
                $notificationRefusee->setDate($now);
                $entityManager->persist($notificationRefusee);
                $this->addFlash("warning", "Offre concernant le produit \"" . $offre->getProduits()->getNomProduit() . "\" refusée");
                break;
            default:
                throw new \InvalidArgumentException("Statut invalide");
        }

        // Envoyer des notifications par e-mail, etc.

        $entityManager->flush();

        // Rediriger l'utilisateur vers la page du profil
        return $this->redirectToRoute('show_home');
    }



    #[Route('/marquer-offre-lue/{id}', name: 'marquer_offre_lue')] // offre lue 
    public function marquerOffreLue(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        // Mettre à jour l'état isRead dans la base de données
        $notification->setIsRead(true);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
