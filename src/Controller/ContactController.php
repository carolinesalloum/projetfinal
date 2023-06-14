<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use App\Repository\CategoryRepository;
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
    public function contactApp(CategoryRepository $categoryRepository,Request $request, MailerInterface $mailer): Response
    {

        $user= $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Veuillez vous connecter pour nous contacter.');
            return $this->redirectToRoute('app_login');
        }
        $categories = $categoryRepository->findAll();
      
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $address = $data['email'];
            $content = $data['content'];

            $email = (new Email())
            ->from($address)
            ->to('alsalloumcaroline@gmail.com')
            ->subject('Demand de contact')
            ->text($content);
            
        $mailer->send($email);
        $this->addFlash('success','votre message a été envoyé');
        //Après le transfert de notre Entity User, on retourne sur le login
        return $this->redirectToRoute('app_index');
        }
        return $this->renderForm('contact/contact.html.twig', [
            'formulaire' => $form,
            'categories' => $categories
        ]);
    }
}
