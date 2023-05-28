<?php

namespace App\Controller;

use DateTime;
use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        
        $messages = $doctrine->getRepository(Message::class)->findBy([
            'expediteur' => $user
        ], ['date' => 'DESC']);

        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'messages' => $messages,
        ]);
    }

    #[Route('/home/add', name: 'add_message')] // ajouter un message
    #[Route('/home/{id}/edit', name: 'edit_message')] // modifier un message
    public function add(ManagerRegistry $doctrine, Message $message = null,  Request $request): Response
    {


        $user = $this->getUser(); // récupérer objet user 

        if (!$message) { // edit
            $message = new Message();
        } //add


        $form = $this->createForm(MessageType::class, $message,  []);
        $form->handleRequest($request); // analyse the request

        if ($form->isSubmitted() && $form->isValid()) { // valid respecter les contraintes
            // on recupere les images transmisse
        
            $message = $form->getData();

            $message->setUser($user); // install mon user
            $now = new DateTime(); // objet date
            $message->setDateCreationmessage($now); // installe ma date
            $message->setEtat(false);

            $entityManager = $doctrine->getManager(); // on récupère les ressources
            $entityManager->persist($message); // on enregistre la ressource
            $entityManager->flush(); // on envoie la ressource insert into

            return $this->redirectToRoute('show_home');
        }

        return $this->render('message/add.html.twig', [
            'formAddmessage' => $form->createView(), // généré le visuel du form
            "edit" => $message->getId(),
            "messages" => $message,


        ]);
    }
}
