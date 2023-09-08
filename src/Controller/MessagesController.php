<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Messages;
use App\Form\MessagesType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagesController extends AbstractController
{
    #[Route('/messages', name: 'app_messages')]
    public function index(): Response
    {
        return $this->render('messages/index.html.twig', [
            'controller_name' => 'MessagesController',
        ]);
    }

    #[Route('/messages/sent', name: 'sent_messages')]
    public function sent(): Response
    {
        return $this->render('messages/sent.html.twig', []);
    }

    #[Route('/send/{vendorId}', name: 'send')]
    public function send(Request $request, ManagerRegistry $doctrine, $vendorId): Response
    {
        $user = $this->getUser();

        $vendor = $doctrine->getRepository(User::class)->find($vendorId);
        //dd($vendor);
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($this->getUser());
            $message->setRecipient($vendor);
            $message->setDate(new \DateTime);
            $message->setIsRead(0);

            $em = $doctrine->getManager();
            $em->persist($message);
            $em->flush();

            $this->addFlash("message", "Message envoyé avec succès", "success");
            return $this->redirectToRoute('app_messages');
        }

        return $this->render('messages/send.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    #[Route('/messages/show/{id}', name: 'show_messages')]
    public function show(Messages $message, ManagerRegistry $doctrine): Response
    {
        $message->setIsRead(1);
        $em = $doctrine->getManager();
        $em->persist($message);
        $em->flush();

        return $this->render('messages/show.html.twig', compact("message"));
    }

    #[Route('/messages/delete/{id}', name: 'delete_messages')]
    public function delete(Messages $message, ManagerRegistry $doctrine): Response
    {

        $em = $doctrine->getManager();
        $em->remove($message);
        $em->flush();

        return $this->redirectToRoute('app_messages');
    }
}
