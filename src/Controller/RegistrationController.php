<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\User;
use App\Entity\Commande;
use App\Entity\Messages;
use App\Entity\Categorie;
use App\Form\EditProfileType;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $categories = $doctrine->getRepository(Categorie::class)->findBy([], []);
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('admin@worldofpets.com', 'Admin Site'))
                    ->to($user->getEmail())
                    ->subject('Confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'categories' => $categories
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre email a été verifié avec succés.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/registration/edit', name: 'edit_user')] // user supprime son compte
    public function editUser(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $this->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('show_home');
        }

        return $this->render('registration/editProfile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/home/delete', name: 'delete_user')] // user supprime son compte
    public function deleteUser(ManagerRegistry $doctrine, SessionInterface $session, User $user = null): Response
    {

        $user = $this->getUser();
        $userAnonyme = $doctrine->getRepository(User::class)->findOneBy(["pseudo" => "anonyme"]);

        if ($user) {
            $entityManager = $doctrine->getManager();
            // Réaffecter les avis associés à l'utilisateur
            $avis = $entityManager->getRepository(Avis::class)->findBy(['users' => $user]);
            foreach ($avis as $avi) {
                $avi->setUsers($userAnonyme);
            }
            // Réaffecter les messages envoyé
            $messageSend = $entityManager->getRepository(Messages::class)->findBy(['sender' => $user]);
            foreach ($messageSend as $send) {
                $send->setSender($userAnonyme);
            }

            // Réaffecter les messags reçus
            $messageRecipient = $entityManager->getRepository(Messages::class)->findBy(['recipient' => $user]);
            foreach ($messageRecipient as $recipient) {
                $recipient->setRecipient($userAnonyme);
            }

            // Réaffecter les commandes
            $commandes = $entityManager->getRepository(Commande::class)->findBy(['commander' => $user]);
            foreach ($commandes as $commande) {
                $commande->setCommander($userAnonyme);
            }

            // Vider la session
            $session = new Session();
            $session->invalidate();

            // Supprimer le user
            $entityManager->remove($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}


