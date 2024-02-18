<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="app_contact")
     */
    public function contactApp(Request $request, MailerInterface $mailer ,Contact $contact = null, EntityManagerInterface $manager): Response
    {
        //MailerInterface est une interface fournie par le composant Mailer de Symfony. Il définit le contrat d'envoi des emails.
    

          //on récupère l'utilisateur authentifié
        $user= $this->getUser();

        // Vérifie si l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour nous contacter.');
            return $this->redirectToRoute('app_login');
        }
        // Vérifie si l'utilisateur est vérifié
    //     if (!$user->getIsVerified()) {
    //         return $this->redirectToRoute('nonvalide');
    // }
    // Crée une nouvelle instance de l'entité Contact
        $contact =new contact;
      
      // Crée le formulaire en utilisant le type de formulaire ContactType
        $form = $this->createForm(ContactType::class, $contact);

        // Traite la soumission du formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if($form->isSubmitted() && $form->isValid()){

           // Persiste l'entité Contact dans la base de données
            $manager->persist($contact);

          // Exécute la requête
            $manager->flush();

             // Récupère le contenu du message de contact soumis via le formulaire
            $content = $contact->getContent();

                // Récupère l'adresse e-mail de l'utilisateur actuellement authentifié
            $userMail = $this->getUser()->getUsername();
            // dd($userMail);
             // Crée un objet Email pour envoyer le message
            $email = (new Email())
            ->from($userMail)
            ->to('info@francoarabophone.fr')
            ->subject('Demand de contact')
            ->text($content);

          //envoyer le mail  
        $mailer->send($email);
         // Ajoute un message flash pour indiquer que le message a été envoyé avec succès
        $this->addFlash('success','votre message a été envoyé');

        // Redirige vers la page d'accueil
        return $this->redirectToRoute('app_index');
        }
        // Rend le formulaire dans le template
        return $this->renderForm('contact/contact.html.twig', [
            'form' => $form,
            
        ]);
    }
}