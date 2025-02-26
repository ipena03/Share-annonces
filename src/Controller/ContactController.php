<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ContactRepository;

final class ContactController extends AbstractController
{
    #[Route('/mod-liste-contact', name: 'app_liste-contacts')]
    public function index(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findAll();
        return $this->render('contact/liste-contact.html.twig',[
        'contacts' => $contacts 
            
        ]);
    }



}
