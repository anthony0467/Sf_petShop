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
        return $this->render('slider/index.html.twig', [
            'controller_name' => 'SliderController',
        ]);
    }
