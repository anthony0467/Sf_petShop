<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\User;
use App\Form\AvisType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AvisController extends AbstractController
{
    #[Route('/avis', name: 'app_avis')]
    public function index(): Response
    {
        return $this->render('avis/index.html.twig', [
            'controller_name' => 'AvisController',
        ]);
    }

    #[Route('/avis/add/{vendorId}', name: 'add_avis')]
    public function addAvis(ManagerRegistry $doctrine, $vendorId, Request $request): Response
    {
        $user = $this->getUser();
        $vendor = $doctrine->getRepository(User::class)->find($vendorId);
        // dd($vendor);

        $avis = new Avis();
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avis->setUsers($user);
            $avis->setVendeur($vendor);
            $avis->setDateAvis(new \DateTime);
            $avis->setActif(0);

            $em = $doctrine->getManager();
            $em->persist($avis);
            $em->flush();


            return $this->redirectToRoute('show_avis', ['vendorId' => $vendorId]);
        }

        return $this->render('avis/add.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/avis/show/{vendorId}', name: 'show_avis')]
    public function showAvis(ManagerRegistry $doctrine, $vendorId, $vendeur = null): Response
    {

        $vendeur = $doctrine->getRepository(User::class)->find($vendorId); // info du vendeur

        $avis = $doctrine->getRepository(Avis::class)->findBy(['Vendeur' => $vendorId], ["dateAvis" => "ASC"]);

        if ($vendeur) {

            return $this->render('avis/show.html.twig', [
                'controller_name' => 'AvisController',
                'avis' => $avis,
                'vendeur' => $vendeur
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/home/delete/{id}/{vendorId}', name: 'delete_avis')] // supprimer le commentaire
    public function delete(ManagerRegistry $doctrine, $vendorId,  Avis $avis = null): Response
    {

        if ($avis) {
            $entityManager = $doctrine->getManager();

            // Supprimer le produit
            $entityManager->remove($avis);
            $entityManager->flush();

            return $this->redirectToRoute('show_avis', ['vendorId' => $avis->getUsers()->getId()]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
    #[Route('/avis/delete-reply/{vendorId}/{parentId}', name: 'delete_reply')]
    public function deleteReply(ManagerRegistry $doctrine, $vendorId, $parentId): Response
    {
        $parentAvis = $doctrine->getRepository(Avis::class)->find($parentId);
        //dd($parentAvis);
        if ($parentAvis) {
            $entityManager = $doctrine->getManager();

            // Supprimer le commentaire parent
            $entityManager->remove($parentAvis);
            $entityManager->flush();

            return $this->redirectToRoute('show_avis', ['vendorId' => $vendorId]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}
