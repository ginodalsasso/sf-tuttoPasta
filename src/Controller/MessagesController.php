<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

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
    public function sendMessage(Request $request,EntityManagerInterface $entityManager, Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message->setSender($this->getUser());

            $message = $form->getData();
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message envoyé avec succès');
            return $this->redirectToRoute('app_messages');
        } 

        return $this->render('messages/send.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reply/{id}', name: 'app_reply')]
    public function reply(Message $originalMessage, Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Vérifie que l'utilisateur est autorisé à répondre
        if ($user !== $originalMessage->getSender() && $user !== $originalMessage->getRecipient()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à répondre à ce message.');
        }

        $reply = new Message();
        $reply->setSender($user);
        // Si l'utilisateur est l'expéditeur, le destinataire est le récepteur et vice versa
        $reply->setRecipient($user === $originalMessage->getSender() ? $originalMessage->getRecipient() : $originalMessage->getSender()); 
        $form = $this->createForm(MessageType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setContent($form->get('content')->getData());

            $entityManager->persist($reply);
            $entityManager->flush();

            $this->addFlash('success', 'Réponse envoyée avec succès');
            return $this->redirectToRoute('app_received');
        }

        return $this->render('messages/reply.html.twig', [
            'form' => $form->createView(),
            'originalMessage' => $originalMessage,
        ]);
    }


    #[Route('/received', name: 'app_received')]
    public function received(Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        return $this->render('messages/received.html.twig');
    }

    #[Route('/read/{id}', name: 'app_read')]
    public function read(Message $message, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }
        
        $message->setRead(true);
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->render('messages/read.html.twig', compact('message'));
    }

    #[Route('/delete/{id}', name: 'app_deleteMessage')]
    public function deleteMessage(Message $message, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_received');
    }

    #[Route('/sent', name: 'app_sent')]
    public function sent(Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        return $this->render('messages/sent.html.twig');
    }
    
}
