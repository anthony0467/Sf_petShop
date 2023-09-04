<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\User;
use App\Form\AvisType;
use App\Repository\AvisRepository;
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
    public function addAvis(ManagerRegistry $doctrine, Avis $avis = null, $vendorId, Request $request): Response
    {


        $user = $this->getUser();
        $vendor = $doctrine->getRepository(User::class)->find($vendorId);


        if (!$vendor) { // si le vendeur n'existe pas
            throw $this->createNotFoundException('Le vendeur spécifié n\'existe pas.');
        }
        // dd($vendor);
        if (!$avis) {
            $avis = new Avis();
        }

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
            'form' => $form->createView(),
            'avis' => $avis->getParent(),
        ]);
    }

    #[Route('/avis/publish/{id}', name: 'publish_avis')] // publier commentaire
    //#[IsGranted('ROLE_ADMIN')]
    public function publishAvis(ManagerRegistry $doctrine, Avis $avis)
    {


        if ($avis->isActif()) {
            $avis->setActif(false);
        } else {
            $avis->setActif(true);
        }

        $em = $doctrine->getManager();
        $em->flush();

        return $this->redirectToRoute('show_admin');
    }



    #[Route('/avis/show/{vendorId}', name: 'show_avis')]
    public function showAvis(ManagerRegistry $doctrine, $vendorId, AvisRepository $Ar, $vendeur = null): Response
    {

        $vendeur = $doctrine->getRepository(User::class)->find($vendorId); // info du vendeur

        //$avis = $doctrine->getRepository(Avis::class)->findBy(['Vendeur' => $vendorId], ["dateAvis" => "ASC"]);
        $avisActif = $Ar->actifAvisVendor($vendorId);

        if ($vendeur) {

            return $this->render('avis/show.html.twig', [
                'controller_name' => 'AvisController',
                'avis' => $avisActif,
                'vendeur' => $vendeur
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/home/delete/{id}/{vendorId}', name: 'delete_avis')] // supprimer le commentaire
    public function deleteAvis(ManagerRegistry $doctrine, $vendorId,  Avis $avis = null): Response
    {

        if ($avis) {
            $entityManager = $doctrine->getManager();

            // Supprimer le avis
            $entityManager->remove($avis);
            $entityManager->flush();

            return $this->redirectToRoute('show_avis', ['vendorId' => $avis->getUsers()->getId()]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/avis/admin', name: 'admin_avis')] // gerer les avis admin
    public function adminAvis(ManagerRegistry $doctrine, AvisRepository $Ar): Response
    {
        $avisInactif = $Ar->inactifAvis();
        $avisActif = $Ar->actifAvis();

        return $this->render('avis/admin.html.twig', [
            'controller_name' => 'AvisController',
            'avisInactif' => $avisInactif,
            'avisActif' => $avisActif
        ]);
    }

    #[Route('/avis/add-reply/{vendorId}/{parentId}', name: 'add_reply')] // reponse commentaire
    public function addReply(ManagerRegistry $doctrine, $vendorId, $parentId, Request $request): Response
    {
        $user = $this->getUser();
        $vendor = $doctrine->getRepository(User::class)->find($vendorId);
        $parentAvis = $doctrine->getRepository(Avis::class)->find($parentId);

        if (!$vendor) {
            throw $this->createNotFoundException('Le vendeur spécifié n\'existe pas.');
        }

        if (!$parentAvis) {
            throw $this->createNotFoundException('Le commentaire parent spécifié n\'existe pas.');
        }

        $avis = new Avis();
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avis->setActif(0);
            $avis->setUsers($user);
            $avis->setVendeur($vendor);
            $avis->setDateAvis(new \DateTime());
            $avis->setActif(false);
            $parentAvis->addReponse($avis);

            $em = $doctrine->getManager();
            $em->persist($avis);
            $em->flush();

            return $this->redirectToRoute('show_avis', ['vendorId' => $vendorId]);
        }

        return $this->render('avis/add.html.twig', [
            'form' => $form->createView(),
            'avis' => $parentId,
        ]);
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
