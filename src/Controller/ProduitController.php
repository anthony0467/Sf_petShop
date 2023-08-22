<?php

namespace App\Controller;


use App\Entity\User;
use App\Entity\Offre;
use App\Entity\Produit;
use App\Form\OffreType;
use App\Form\OrderType;
use App\Entity\Commande;
use App\Entity\Categorie;
use App\Entity\Notification;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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

    #[Route('/produit/show/{id}', name: 'show_produit')] // vue detaillé du produit
    public function show(ManagerRegistry $doctrine,  Produit $produit = null,): Response
    {
        $user = $this->getUser();



        if ($produit) {

            // $message->setExpediteur($user);
            //$message->setDestinataire($vendeur);

            return $this->render('produit/show.html.twig', [
                'controller_name' => 'HomeController',
                'produit' => $produit,



            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }


    #[Route('/produit/order/{id}', name: 'show_order')] // vue de la commande
    public function showOrder(ManagerRegistry $doctrine,  Produit $produit = null, Request $request): Response
    {
        $user = $this->getUser();

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
                $entityManager = $doctrine->getManager(); // on récupère les ressources
                $entityManager->persist($commande); // on enregistre la ressource
                $entityManager->flush(); // on envoie la ressource insert into

                return $this->redirectToRoute('app_home');
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

    #[Route('/offre/{vendorId}/{productId}', name: 'show_offre')] // formulaire offre 
    public function send(Request $request, ManagerRegistry $doctrine, $vendorId, $productId): Response
    {
        $user = $this->getUser();
        $offre = new Offre();

        $produit = $doctrine->getRepository(Produit::class)->find($productId);
        $offreRepository = $doctrine->getRepository(Offre::class);

        // Vérifier si l'utilisateur a déjà fait une offre pour ce produit
        $existingOffre = $offreRepository->findOneBy(['Users' => $user, 'produits' => $produit]);
        if ($existingOffre || $offre->isIsDeleted(false)) {
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
            $notification->setOffre($offre);

            $em->persist($notification);
            $em->flush();

            $this->addFlash("offre", "Offre envoyée avec succès", "success");
            return $this->redirectToRoute('show_home');
        }

        return $this->render('produit/offre.html.twig', [
            "form" => $form->createView(),
        ]);
    }




    #[Route('/changer-statut-offre/{id}/{statut}', name: 'changer_statut_offre')]
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
                // ... Code pour l'offre acceptée ...
                $offre->setStatut(Offre::STATUT_ACCEPTEE);
                // Créer et enregistrer une nouvelle notification pour l'acceptation de l'offre
                $notificationAcceptee = new Notification();
                $notificationAcceptee->setMessage("Votre offre pour le produit \"" . $offre->getProduits()->getNomProduit() . "\" a été acceptée.");
                $notificationAcceptee->setOffre($offre);
                $notificationAcceptee->setUser($user);

                $now = new \DateTime(); // objet date
                $notificationAcceptee->setDate($now);

                $entityManager->persist($notificationAcceptee);
                break;
            case 'refusee':
                // Créer et enregistrer une nouvelle notification pour le refus de l'offre
                $offre->setStatut(Offre::STATUT_REFUSEE);
                $offre->setIsDeleted(true);
                $notificationRefusee = new Notification();
                $notificationRefusee->setMessage("Votre offre pour le produit \"" . $offre->getProduits()->getNomProduit() . "\" a été refusée.");
                $notificationRefusee->setOffre($offre);
                $notificationRefusee->setUser($user);
                $now = new \DateTime(); // objet date
                $notificationRefusee->setDate($now);
                $entityManager->persist($notificationRefusee);

                break;
            default:
                throw new \InvalidArgumentException("Statut invalide");
        }

        // Envoyer des notifications par e-mail, etc.

        $entityManager->flush();

        // Rediriger l'utilisateur vers la page du profil
        return $this->redirectToRoute('show_home');
    }



    #[Route('/marquer-offre-lue/{id}', name: 'marquer_offre_lue')]
    public function marquerOffreLue(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        // Mettre à jour l'état isRead dans la base de données
        $notification->setIsRead(true);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
