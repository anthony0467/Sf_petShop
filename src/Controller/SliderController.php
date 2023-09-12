<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Slider;
use App\Form\SliderType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SliderController extends AbstractController
{
    #[Route('/slider', name: 'app_slider')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/slider/add', name: 'add_slide')] // ajouter un slide
    #[Route('/slider/{id}/edit', name: 'edit_slide')] // modifier un slide
    public function add(ManagerRegistry $doctrine, Slider $slider = null,  Request $request): Response
    {

        $entityManager = $doctrine->getManager(); // on récupère les ressources

        if (!$slider) { // edit
            $slider = new Slider();
        } //add


        $form = $this->createForm(SliderType::class, $slider,  []);
        $form->handleRequest($request); // analyse the request


        if ($form->isSubmitted() && $form->isValid()) { // valid respecter les contraintes
            // on recupere les images transmisse
            $images = $form->get('images')->getData();

            /*  foreach ($slider->getImage() as $image) {
                $slider->removeImage($image);
                $entityManager->remove($image); // Supprimer l'image de la base de données si nécessaire

            }*/

            // on boucle sur les tableaux
            foreach ($images as $image) {
                // on genere un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                //on copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                //on stocke l'image dans la base de données
                $img = new Images();
                $img->setNomImage($fichier);
                $slider->addImage($img);
            }

            $slider = $form->getData();

            // $evenement->setUser($user); // install mon user
            // $now = new \DateTime(); // objet date
            //$evenement->setDateCreationevenement($now); // installe ma date



            $entityManager->persist($slider); // on enregistre la ressource
            $entityManager->flush(); // on envoie la ressource insert into

            return $this->redirectToRoute('app_home');
        }

        return $this->render('slider/add.html.twig', [
            'formAddSlider' => $form->createView(), // généré le visuel du form
            "edit" => $slider->getId(),
            "slider" => $slider,


        ]);
    }

    #[Route('/slider/delete/{id}', name: 'delete_slider')] // supprimer evenement
    public function delete(ManagerRegistry $doctrine, Slider $slider = null): Response
    {
        if ($slider) {
            $entityManager = $doctrine->getManager();

            // Supprimer les images associées
            $images = $slider->getImage();
            foreach ($images as $image) {
                // Supprimer le fichier du dossier
                $imagePath = $this->getParameter('images_directory') . '/' . $image->getNomImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $entityManager->remove($image);
            }

            // Supprimer le produit
            $entityManager->remove($slider);
            $entityManager->flush();

            return $this->redirectToRoute('show_admin');
        } else {
            return $this->redirectToRoute('show_admin');
        }
    }
}
