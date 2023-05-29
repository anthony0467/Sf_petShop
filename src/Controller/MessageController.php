<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
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
        $message = $this->getUser();
        
        $messages = $doctrine->getRepository(Message::class)->findBy([
            'expediteur' => $message
        ], ['date' => 'DESC']);

        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'messages' => $messages,
        ]);
    }

    #[Route('/message/add/{destinataireId}', name: 'add_message')]
#[Route('/message/{id}/edit', name: 'edit_message')]
public function add(ManagerRegistry $doctrine, Request $request, $destinataireId, MessageRepository $mp ): Response
{
    $currentUser = $this->getUser(); // Récupérer l'utilisateur courant
    $destinataire = $doctrine->getRepository(User::class)->find($destinataireId);
    $conversations = $mp->findByConversation($currentUser, $destinataire);
    //dd($destinataire);
    $message = new Message();

    $form = $this->createForm(MessageType::class, $message, []);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
       
        $message = $form->getData();

        $message->setExpediteur($currentUser);
        // Vous devez définir le destinataire en utilisant la logique appropriée
        $message->setDestinataire($destinataire);

        $now = new DateTime();
        $message->setDate($now);
    

        $entityManager = $doctrine->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('show_message', ['destinataireId' => $destinataireId]);

    }

    return $this->render('message/add.html.twig', [
        'formAddMessage' => $form->createView(),
        "edit" => $message->getId(),
        "messages" => $message,
        'destinataireId' => $destinataire->getId(),
    ]);
}


    #[Route('/message/show/{destinataireId}', name: 'show_message')] // afficher autre utilisateur
    public function showmessage(ManagerRegistry $doctrine, Message $message = null, $destinataireId, MessageRepository $mp): Response
    {
        $expediteur = $this->getUser();
       
        $destinataire = $doctrine->getRepository(User::class)->find($destinataireId);
        //dd($destinataire);
        if ($destinataire) {

            if (!$destinataire) {
                throw $this->createNotFoundException('Destinataire non trouvé');
            }
        
            //$messages = $doctrine->getRepository(Message::class)->findByConversation($destinataire->getConversationId());
            $messages = $mp->findByConversation($expediteur, $destinataire);
            
            return $this->render('message/show.html.twig', [
                'expediteur' => $expediteur,
                'destinataire' => $destinataire,
                'messages' => $messages,
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}
