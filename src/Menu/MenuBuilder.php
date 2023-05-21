<?php 

namespace App\Menu;

use App\Entity\Categorie;
use Knp\Menu\ItemInterface;
use Knp\Menu\FactoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class MenuBuilder
{
    private $factory;
    private $em;

    /**
     * Add any other dependency you need...
     */
    public function __construct(FactoryInterface $factory, EntityManagerInterface $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Accueil', ['route' => 'app_home']);
        
        $categories = $this->em->getRepository(Categorie::class)->findBy([], []);

        //$categoryMenu = $this->createCategoryMenu($categories); autre maniere, sous menu catégories
       // $menu->addChild($categoryMenu);

       foreach ($categories as $category) {
        $menu->addChild($category->getNomCategorie(), [
            'route' => 'show_categorie',
            'routeParameters' => ['id' => $category->getId()]
        ]);
    }
        $menu->addChild('Évenements', ['route' => 'app_evenement']);

        return $menu;
    }

  /*  public function createCategoryMenu(array $categories): ItemInterface
{
    $menu = $this->factory->createItem('categories');

    foreach ($categories as $category) {
        $menu->addChild($category->getnomCategorie(), [
            'route' => 'show_categorie',
            'routeParameters' => ['id' => $category->getId()]
        ]);
    }

    return $menu;
}*/

}