<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Categorie;
use App\Entity\Evenement;
use App\Form\EvenementType;
use App\HttpClient\NominatimHttpClient;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement')]
    public function index(ManagerRegistry $doctrine): Response
    {

        $evenements = $doctrine->getRepository(Evenement::class)->findBy([], ["dateEvenement" => "DESC"]);

        return $this->render('evenement/index.html.twig', [
            'controller_name' => 'EvenementController',
            'evenements' => $evenements
        ]);
    }



    #[Route('/evenement/add', name: 'add_evenement')] // ajouter un evenement
    #[Route('/evenement/{id}/edit', name: 'edit_evenement')] // modifier un evenement
    public function add(ManagerRegistry $doctrine, Evenement $evenement = null, NominatimHttpClient $nominatim,  Request $request): Response
    {


        if (!$evenement) { // edit
            $evenement = new Evenement();
        } //add


        $form = $this->createForm(EvenementType::class, $evenement,  []);
        $form->handleRequest($request); // analyse the request


        if ($form->isSubmitted() && $form->isValid()) { // valid respecter les contraintes
            // on recupere les images transmisse
            $images = $form->get('images')->getData();

            $adresse = urlencode($form->get('localisation')->getData());
            $ville = urlencode($form->get('ville')->getData());
            $cp = $form->get('cp')->getData();

            // On passe les données a nominatim
            $coordinates = $nominatim->getCoordinates($adresse,  $ville, $cp);
            //dd($coordinates);
            if (!empty($coordinates)) {
                $evenement->setLatitude($coordinates[0]['lat']);
                $evenement->setLongitude($coordinates[0]['lon']);
            } else {
                // Gérer le cas où les coordonnées ne sont pas trouvées
                // Par exemple, définir des valeurs par défaut ou afficher un message d'erreur
                $evenement->setLatitude(0); // Valeur par défaut pour la latitude
                $evenement->setLongitude(0); // Valeur par défaut pour la longitude
                // Autres actions à prendre
            }

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
                $evenement->addImage($img);
            }

            $evenement = $form->getData();

            // $evenement->setUser($user); // install mon user
            // $now = new \DateTime(); // objet date
            //$evenement->setDateCreationevenement($now); // installe ma date


            $entityManager = $doctrine->getManager(); // on récupère les ressources
            $entityManager->persist($evenement); // on enregistre la ressource
            $entityManager->flush(); // on envoie la ressource insert into

            return $this->redirectToRoute('app_evenement');
        }

        return $this->render('evenement/add.html.twig', [
            'formAddEvenement' => $form->createView(), // généré le visuel du form
            "edit" => $evenement->getId(),
            "evenements" => $evenement,


        ]);
    }

    #[Route('/evenement/delete/{id}', name: 'delete_evenement')] // supprimer evenement
    public function delete(ManagerRegistry $doctrine, Evenement $evenement = null): Response
    {
        if ($evenement) {
            $entityManager = $doctrine->getManager();

            // Supprimer les images associées
            $images = $evenement->getImages();
            foreach ($images as $image) {
                $imagePath = $this->getParameter('images_directory') . '/' . $image->getNomImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $entityManager->remove($image);
            }

            // Supprimer le produit
            $entityManager->remove($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement');
        } else {
            return $this->redirectToRoute('app_evenement');
        }
    }

    #[Route('/evenement/show/{id}', name: 'show_evenement')] // afficher evenement detail
    public function showUser(ManagerRegistry $doctrine, Evenement $evenement = null): Response
    {

        $evenements = $doctrine->getRepository(Evenement::class)->findBy([]);
        if ($evenement) {

            $latitude = $evenement->getLatitude();
            $longitude = $evenement->getLongitude();
            // Ajoutez les coordonnées à un tableau
            $coordinates[] = [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];

            return $this->render('evenement/show.html.twig', [
                'evenement' => $evenement,
                'evenements' => $evenements,
                'coordinates' => $coordinates,
            ]);
        } else {
            return $this->redirectToRoute('app_evenement');
        }
    }
}
