<?php

namespace App\Security;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Redirect500
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Récupération de l'exception
        $exception = $event->getThrowable();

        // Vérification si l'exception est de type HttpExceptionInterface
        if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() === 500) {
            $response = new RedirectResponse($this->router->generate('app_home'));
            $event->setResponse($response);
        }
        // Si l'exception n'est pas de type HttpExceptionInterface mais est une erreur 500
        else if (!$exception instanceof HttpExceptionInterface && $exception->getCode() === 500) {
            $response = new RedirectResponse($this->router->generate('app_home'));
            $event->setResponse($response);
        }
    }
}
