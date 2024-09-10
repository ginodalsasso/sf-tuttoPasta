<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagesController extends AbstractController
{
    #[Route('/messages', name: 'app_messages')]
    public function index(): Response
    {
        return $this->render('messages/index.html.twig', [
            'controller_name' => 'MessagesController',
        ]);
    }
    
    
    #[Route('/sendMessage', name: 'app_sendMessage')]
    public function sendMessage(Request $request): Response
    {

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        return $this->render('messages/send.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
