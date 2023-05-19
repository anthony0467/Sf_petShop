<?php

namespace App\HttpClient;

use App\Factory\XmlResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * Class NominatimHttpClient
 * @package App\Client
 */

class NominatimHttpClient extends AbstractController
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

     /**
     * NominatimHttpClient constructor.
     *
     * @param HttpClientInterface $nominatim
     */

    public function __construct(HttpClientInterface $nominatim)
    {
        $this->httpClient = $nominatim;
    }

    // Récuperer les coordronées d'un lieu
    public function getCoordinates($adresse, $ville, $cp)
    {   
        // Search avec adresse, ville, cp. Renvoie un seul résulat en JSON. 
        $response = $this->httpClient->request('GET', "/search?street=$adresse&city=$ville&postalcode=$cp&limit=1&format=json", [
            'verify_peer' => false, 
        ]);

        // On récupere le contenu JSON de la réponse et on la converti en tableau PHP avec json_decode. True renvoie un tableau associatif plutot qu'un objet. 
        $coordinates = json_decode($response->getContent(), true);

        return $coordinates;
    }

}