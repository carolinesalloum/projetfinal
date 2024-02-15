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
        //Une instance de l'entité Contact est créée.Celui-ci sera utilisé pour stocker les données du formulaire de contact.
        $contact =new contact;
        //on récupère l'utilisateur authentifié
        $user= $this->getUser();
        //Il vérifie s'il existe un utilisateur authentifié. Si ce n'est pas le cas, 
        //il ajoute un message flash indiquant que l'utilisateur doit être connecté et redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour nous contacter.');
            return $this->redirectToRoute('app_login');
        }
      
        // $contactRepo = $repo->findAll();
        // dd($contactRepo);
        //Un formulaire est créé à l'aide du type de formulaire ContactType. Ce formulaire est utilisé pour collecter des informations de contact.
        $form = $this->createForm(ContactType::class, $contact);
        // La méthode handleRequest est appelée sur le formulaire pour traiter la soumission du formulaire.
        $form->handleRequest($request);
        // condition de soumission et de validité du formulaire
        if($form->isSubmitted() && $form->isValid()){

              // On demande au manager de préparer la requête
            $manager->persist($contact);
            //  On execute
            $manager->flush();

            $content = $contact->getContent();//  récupérer le contenu du message de contact soumis via le formulaire de contact.
            $userMail = $this->getUser()->getUsername();// utilisé pour récupérer l'adresse e-mail de l'utilisateur actuellement authentifié.
            // dd($userMail);
            $email = (new Email())
            ->from($userMail)
            ->to('info@francoarabophone.fr')
            ->subject('Demand de contact')
            ->text($content);
            
        $mailer->send($email);//envoyer le mail
        $this->addFlash('success','votre message a été envoyé');
        //Après le transfert de notre Entity User, on retourne sur le login
        return $this->redirectToRoute('app_index');
        }
        return $this->renderForm('contact/contact.html.twig', [
            'form' => $form,
            
        ]);
    }
}