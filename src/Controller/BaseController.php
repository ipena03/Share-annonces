<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\FileType;
use App\Entity\Fichier;


use App\Form\AnnonceType;
use App\Entity\Annonce;

use Symfony\Component\String\Slugger\SluggerInterface;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted; // Pour restreindre l'accès aux utilisateurs connectés


final class BaseController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]    
    public function index(): Response
    {
        return $this->render('base/index.html.twig', [
        ]);
    }

    #[Route('/private-contact', name: 'app_contact')]
    public function contact(Request $request,  EntityManagerInterface $em): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class,  $contact);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted()&&$form->isValid()){
                $contact->setDateEnvoi(new \Datetime());
                $em->persist($contact);
                $em->flush();
                $this->addFlash('notice','Message envoyé');
                return $this->redirectToRoute('app_contact');
            }
            }
           
    return $this->render('contact/contact.html.twig', [
        'form' => $form->createView()

   
    ]);
 }

 #[Route('/à-propos', name: 'app_à-propos')]
    public function à_propos(): Response
    {
    return $this->render('base/à_propos.html.twig', [
   
    ]);
    

 }

 #[Route('/mentions-légales', name: 'app_mentions-légales')]
    public function mentions_légales(): Response
    {
    return $this->render('base/mentions_légales.html.twig', [
   
    ]);
 }
 #[Route('/private-fichier', name: 'app_fichier')]
 public function fichier(Request $request, EntityManagerInterface $em): Response
 {
     $fichier = new Fichier(); // Crée une nouvelle instance de l'entité
     $form = $this->createForm(FileType::class, $fichier);
 
     $form->handleRequest($request);
 
     if ($form->isSubmitted() && $form->isValid()) {
         // Gestion du fichier uploadé
         $uploadedFile = $form->get('file')->getData(); // Récupère le fichier du formulaire
         if ($uploadedFile) {
             // Générer un nom unique pour le fichier
             $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
             $newFilename = $originalFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
 
             // Déplacer le fichier dans le répertoire défini
             $uploadedFile->move(
                 $this->getParameter('upload_directory'), // Répertoire défini dans services.yaml
                 $newFilename
             );
 
             // Mettre à jour l'entité avec les informations du fichier
             $fichier->setRouteFichier($newFilename); // Sauvegarde le chemin relatif
         }
 
         // Sauvegarde dans la base de données
         $fichier->setDateEnvoi(new \Datetime());

         $em->persist($fichier);
         $em->flush();
 
         // Ajoute un message de confirmation
         $this->addFlash('notice', 'Fichier envoyé avec succès !');
 
         return $this->redirectToRoute('app_fichier');
     }
 
     // Afficher le formulaire
     return $this->render('fichier/fichier.html.twig', [
         'form' => $form->createView(),
     ]);
 }


 #[Route('/private-annonces', name: 'app_annonces')]
 #[IsGranted('ROLE_USER')] // Empêche les utilisateurs non connectés d'accéder à la page
 public function annonces(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, Security $security): Response
 {
     $user = $security->getUser(); // Récupérer l'utilisateur connecté
 
     if (!$user) {
         throw $this->createAccessDeniedException('Vous devez être connecté pour poster une annonce.');
     }
 
     $annonce = new Annonce();
     $form = $this->createForm(AnnonceType::class, $annonce);
 
     if ($request->isMethod('POST')) {
         $form->handleRequest($request);
 
         if ($form->isSubmitted() && $form->isValid()) {
             $annonce->setDateEnvoi(new \Datetime());
             $annonce->setUser($user); // Associer l'annonce à l'utilisateur connecté
 
             // Gérer l'image uploadée
             $imageFile = $form->get('photo')->getData();
 
             if ($imageFile) {
                 // Créer un nom de fichier unique
                 $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                 $safeFilename = $slugger->slug($originalFilename);
                 $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
 
                 try {
                     // Déplacer le fichier dans le répertoire configuré
                     $imageFile->move(
                         $this->getParameter('upload_directory'), // Utilisation de `upload_directory`
                         $newFilename
                     );
                 } catch (\Exception $e) {
                     $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                     return $this->redirectToRoute('app_annonces');
                 }
 
                 // Associer le nom du fichier à l'entité
                 $annonce->setPhoto($newFilename);
             }
 
             $em->persist($annonce);
             $em->flush();
 
             $this->addFlash('notice', 'Annonce créée avec succès !');
             return $this->redirectToRoute('app_annonces');
         }
     }
 
     return $this->render('annonce/annonces.html.twig', [
         'form' => $form->createView(),
     ]);
 }
  
 


}
