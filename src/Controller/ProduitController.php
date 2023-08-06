<?php

namespace App\Controller;


use App\Entity\User;
use App\Entity\Offre;
use App\Entity\Produit;
use App\Form\OffreType;
use App\Form\OrderType;
use App\Entity\Commande;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

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
        //$vendeur = $doctrine->getRepository(Produit::class)->find($vendorId);
        //$vendor = $doctrine->getRepository(User::class)->find($vendorId);
        $offre = new Offre();
        $produit = $doctrine->getRepository(Produit::class)->find($productId);
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

            $this->addFlash("offre", "Offre envoyé avec succès", "success");
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

        // Mettre à jour le statut de l'offre en fonction de la valeur passée dans l'URL
        switch ($statut) {
            case 'acceptee':
                $offre->setStatut(Offre::STATUT_ACCEPTEE);
                $offre->setNotifStatus(true);
                dump('Envoyer un e-mail ici');
                // Envoi d'un e-mail de notification au client
                $clientEmail = $offre->getUsers()->getEmail();
                $produitNom = $offre->getProduits()->getNomProduit();

                $email = (new Email())
                    ->from('votre_adresse_email@example.com')
                    ->to($clientEmail)
                    ->subject('Votre offre a été acceptée')
                    ->text("Félicitations, votre offre pour le produit $produitNom a été acceptée.");

                $mailer->send($email);
                break;
            case 'refusee':
                $offre->setStatut(Offre::STATUT_REFUSEE);
                break;
            default:
                throw new \InvalidArgumentException("Statut invalide");
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($offre);
        $entityManager->flush();

        // Rediriger l'utilisateur vers la page du profil
        return $this->redirectToRoute('show_home');
    }
}
