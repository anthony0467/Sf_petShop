<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

    #[Route('/contact/add', name: 'send_contact')] // envoyer un message contact
    public function send(Request $request, ManagerRegistry $doctrine): Response
    {

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $em = $doctrine->getManager();
            $em->persist($contact);
            $em->flush();

            $this->addFlash("message", "Message envoyé avec succès", "success");
            return $this->redirectToRoute('app_home');
        }

        return $this->render('contact/add.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    #[Route('/contact/show/{id}', name: 'show_contact')] // detail du message contact
    public function show(Contact $contact, ManagerRegistry $doctrine): Response
    {

        if (!$contact) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('contact/show.html.twig', [
            "contact" => $contact
        ]);
    }

    #[Route('/contact/delete/{id}', name: 'delete_contact')] // supprimer un message contact
    public function delete(Contact $contact, ManagerRegistry $doctrine): Response
    {

        $em = $doctrine->getManager();
        $em->remove($contact);
        $em->flush();

        return $this->redirectToRoute('show_admin');
    }
}
