<?php

namespace App\HttpClient;

use App\Factory\XmlResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * Class GeoGouvHttpClient
 * @package App\Client
 */

class GeoGouvHttpClient extends AbstractController
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

     /**
     * GeoGouvHttpClient constructor.
     *
     * @param HttpClientInterface $geogouv
     */

    public function __construct(HttpClientInterface $geogouv)
    {
        $this->httpClient = $geogouv;
    }

    // Récuperer les propositions d'adresse a partir de la saisie 
    public function getAdresses()
    {   
        // Retourne les adresses qui correspondent a l'adresse tapée 
        $response = $this->httpClient->request('GET',"https://api-adresse.data.gouv.fr/search/?q=$adresse&type=housenumber&autocomplete=1" , [
            'verify_peer' => false, 
        ]);

        // On return le contenu JSON de la réponse et on la converti en tableau PHP avec json_decode. True renvoie un tableau associatif plutot qu'un objet. 
        return json_decode($response->getContent(), true);
    }

}