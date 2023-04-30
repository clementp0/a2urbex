<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{


     #[Route('/chat-add', name: 'save_chat_history', methods: ['GET', 'POST'])]
    public function saveChatHistory(Request $request): Response
    {
        $chatHistory = $request->getContent();
        $filename = 'chat.json';
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
        $file = fopen($filePath, 'a');
        fwrite($file, ',' . PHP_EOL . $chatHistory );
        fclose($file);

        return new Response('Chat history saved.');

    }


    #[Route('/chat-get', name: 'chat_history', methods: ['GET', 'POST'])]
    
    public function getChatHistory(): Response
    {
        $filename = 'chat.json';
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
        $chatHistory = '';
        if (file_exists($filePath)) {
            $file = fopen($filePath, 'r');
            $chatHistory = fread($file, filesize($filePath));
            fclose($file);
        }
        return new Response('[' . $chatHistory . ']');
    }
}