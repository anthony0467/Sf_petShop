<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
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
    public function success(Request $request, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        $produitId = $request->query->get('produitId'); // Récupérer l'identifiant du produit depuis les paramètres de la requête

        $produit = $produitRepository->find($produitId);

        if (!$produit) {
            // Gérer si le produit n'est pas trouvé dans la base de données
            // Par exemple, rediriger l'utilisateur vers une page d'erreur
        }

        // Mettre à jour le champ "disponible" du produit
        $produit->setIsSelling(true); // Ou toute autre logique que vous avez pour le champ disponible
        $entityManager->flush();

        return $this->render('payment/success.html.twig');
    }


    #[Route('/payment/cancel', name: 'app_cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}
