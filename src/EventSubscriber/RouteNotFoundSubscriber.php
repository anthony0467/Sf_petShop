<?php

namespace App\EventSubscriber;


use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteNotFoundSubscriber implements EventSubscriberInterface{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator){
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event){
        $exception = $event->getThrowable();

        if($exception instanceof NotFoundHttpException){
            $redirectUrl = $this->urlGenerator->generate('app_home');

            $response = new RedirectResponse($redirectUrl);

            $event->setResponse($response);
        }
    }

    public static function  getSubscribedEvents(){
        return [
            KernelEvents::EXCEPTION =>  'onKernelException',
        ];
    }
}

 ?>