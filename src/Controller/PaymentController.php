<?php

namespace App\Controller;

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

        if (!$produit) {
            // Gérer si le produit n'est pas trouvé dans la base de données
            return $this->redirectToRoute('app_home');
        }

        $stripeProductId = $produit->getStripeProductId();
        $stripePriceId = $produit->getStripePriceId();

        \Stripe\Stripe::setApiKey('sk_test_51NibhTEctxRE8ZHzRSOxVx6iKTB7WP0MobKRL4IlWwtpmv7jkZ3ORBaS3zmprfTUVWrg6M4kxBrGdTUmTJikf7Xd00Up0YjBEr');

        $YOUR_DOMAIN = 'http://localhost:8000';

        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => [[
                'price' => $stripePriceId,
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN .  $this->generateUrl('app_success', ['produitId' => $produitId]),
            'cancel_url' => $YOUR_DOMAIN . $this->generateUrl('app_cancel'),
        ]);

        return $this->redirect($checkout_session->url);
    }

    #[Route('/payment/success', name: 'app_success')]
    public function success(Request $request, EntityManagerInterface $entityManager, ProduitRepository $produitRepository, MailerInterface $mailer): Response
    {
        $produitId = $request->query->get('produitId'); // Récupérer l'identifiant du produit depuis les paramètres de la requête
        $produit = $produitRepository->find($produitId);
        $user = $this->getUser();

        if (!$produit) {
            // Gérer si le produit n'est pas trouvé dans la base de données
            return $this->redirectToRoute('app_home');
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
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}
