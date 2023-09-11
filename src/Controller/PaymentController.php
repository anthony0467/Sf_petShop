<?php

namespace App\Controller;

use App\Entity\Commande;
use Symfony\Component\Mime\Email;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{

    #[Route('/test/{produitId}', name: 'app_test')]
    public function testRedirect(ManagerRegistry $doctrine, ProduitRepository $produitRepository, Request $request, $produitId): Response
    {

        $produit = $produitRepository->find($produitId);
        $user = $this->getUser();


        if (!$produit) {
            // Gérer si le produit n'est pas trouvé dans la base de données
            return $this->redirectToRoute('app_home');
        }

        $stripeProductId = $produit->getStripeProductId();
        $stripePriceId = $produit->getStripePriceId();

        \Stripe\Stripe::setApiKey('sk_test_51NibhTEctxRE8ZHzRSOxVx6iKTB7WP0MobKRL4IlWwtpmv7jkZ3ORBaS3zmprfTUVWrg6M4kxBrGdTUmTJikf7Xd00Up0YjBEr');

        $YOUR_DOMAIN = 'http://localhost:8000';

        $offreAccepte = $produit->getPrixOffre();

        $checkout_sessions = [];



        // Vérifiez s'il y a des offres pour ce produit
        $produitOffres = $produit->getOffres();

        if (count($produitOffres) > 0) {
            // Il y a des offres, parcourez-les et créez une session pour chaque offre
            foreach ($produitOffres as $offre) {
                // Comparez l'utilisateur de l'offre à l'utilisateur courant
                if ($offre->getUsers() === $user) {
                    // Utilisez le prix de l'offre si elle existe
                    $prix = $offre->getPrix();
                } else {
                    // Utilisez le prix de base
                    $prix = $produit->getPrix();
                }

                // Créez une session Stripe pour cette offre
                $checkout_sessions[] = \Stripe\Checkout\Session::create([
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'eur',
                            'unit_amount' => ($prix + 10) * 100, // Le prix en centimes
                            'product_data' => [
                                'name' => $produit->getNomProduit(),
                            ],
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => $YOUR_DOMAIN .  $this->generateUrl('app_success', ['produitId' => $produitId]),
                    'cancel_url' => $YOUR_DOMAIN . $this->generateUrl('app_cancel'),
                    'shipping_address_collection' => [
                        'allowed_countries' => ['FR'], // Pays autorisés pour la livraison
                    ],
                    'billing_address_collection' => 'required',
                ]);
            }
        } else {
            // Aucune offre, créez une session Stripe pour le prix de base
            $checkout_sessions[] = \Stripe\Checkout\Session::create([
                'line_items' => [
                    [
                        'price' => $stripePriceId, // Utilisez l'identifiant de prix Stripe du produit
                        'quantity' => 1,
                    ],
                    [
                        'price_data' => [
                            'currency' => 'eur', // La devise de vos frais de livraison
                            'unit_amount' => 1000, // Le montant en cents (par exemple, 1000 pour 10 euros)
                            'product_data' => [
                                'name' => 'Frais de livraison', // Nom de l'article de frais de livraison
                            ],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $YOUR_DOMAIN .  $this->generateUrl('app_success', ['produitId' => $produitId]),
                'cancel_url' => $YOUR_DOMAIN . $this->generateUrl('app_cancel'),
                'shipping_address_collection' => [
                    'allowed_countries' => ['FR'], // Pays autorisés pour la livraison
                ],
                'billing_address_collection' => 'required',

            ]);
        }

        // Redirigez l'utilisateur vers la première session de paiement
        return $this->redirect($checkout_sessions[0]->url);
    }

    #[Route('/payment/success', name: 'app_success')]
    public function success(Request $request, ManagerRegistry $doctrine, ProduitRepository $produitRepository, MailerInterface $mailer): Response
    {
        $produitId = $request->query->get('produitId'); // Récupérer l'identifiant du produit depuis les paramètres de la requête
        $produit = $produitRepository->find($produitId);
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();

        // Recherchez la commande en attente associée à l'utilisateur courant
        $commandeRepository = $doctrine->getRepository(Commande::class);
        //dd($commandeRepository);
        $pendingCommande = $commandeRepository->findOneBy(['commander' => $user, 'etat' => Commande::ETAT_EN_ATTENTE]);


        if (!$produit) {
            // Gérer si le produit n'est pas trouvé dans la base de données
            return $this->redirectToRoute('app_home');
        }


        if ($pendingCommande) {
            // Mettez à jour l'état de la commande en "payée" (ou tout autre état approprié)
            $pendingCommande->setEtat(Commande::ETAT_PAYE);

            // Enregistrez les modifications
            $entityManager->flush();
        }

        // Mettre à jour le champ "disponible" du produit
        $produit->setIsSelling(true); // Ou toute autre logique que vous avez pour le champ disponible
        $entityManager->flush();

        $clientEmail = $produit->getUser()->getEmail();
        $produitNom = $produit->getNomProduit();
        $nomVendeur = $produit->getUser()->getPseudo();
        $email = (new Email())
            ->from('admin@worldofpets.com')
            ->to($clientEmail)
            ->subject("WorlfofPets - Félicitation, vous avez vendu \"" . $produitNom . "\".")
            ->html("
        <p><strong>Félicitations !</strong></p>
        <p>Votre annonce a fait sensation ! Vous avez vendu <strong>$produitNom</strong>.</p>
        <p>Merci de préparer le colis et ensuite de vous rendre sur votre espace personnel afin d'indiquer que le colis a bien été envoyé (voir votre espace vendeur). Cela permettra de tenir informé votre client.</p>
        <p>L'équipe WorldofPets</p>
    ");
        $mailer->send($email);

        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        $clientEmail = $user->getEmail();
        $produitNom = $produit->getNomProduit();
        $nomVendeur = $produit->getUser()->getPseudo();
        $emailAcheteur = (new Email())
            ->from('admin@worldofpets.com')
            ->to($clientEmail)
            ->subject("WorlfofPets - Achat effectué concernant le produit: \"" . $produitNom . "\".")
            ->html("
        <p><strong>Merci pour votre achat!</strong></p>
        <p>Nous vous confirmons votre achat auprès du vendeur $nomVendeur. N'hesiter pas à revenir vers lui ou directement sur notre site en cas de problème.</p>
        <p>Vous serez averti dès que $nomVendeur aura procédé à l'envoi du colis.</p>
        <p>L'équipe WorldofPets</p>
    ");
        $mailer->send($emailAcheteur);

        return $this->render('payment/success.html.twig');
    }


    #[Route('/payment/cancel', name: 'app_cancel')]
    public function cancel(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();

        // Recherchez la commande en attente associée à l'utilisateur courant
        $commandeRepository = $doctrine->getRepository(Commande::class);

        $pendingCommande = $commandeRepository->findOneBy(['commander' => $user, 'etat' => Commande::ETAT_EN_ATTENTE]);

        if ($pendingCommande) {
            // Supprimez la commande en attente et ses données associées
            $entityManager->remove($pendingCommande);
            $entityManager->flush();
        }

        // Redirigez l'utilisateur vers une page appropriée
        return $this->redirectToRoute('app_home');
    }
}
