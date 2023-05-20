<?php

// use App\Entity\User;
// use App\Entity\Admin;
// use App\Entity\Category;
// use App\Form\RegistrationType;
// use Doctrine\Persistence\ManagerRegistry;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;
// use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// namespace App\Controller;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// class SecurityController extends AbstractController
// {
  

    namespace App\Controller;
    
    use App\Entity\User;
    use function Sodium\add;
    use App\Form\RegistrationType;
    use App\Repository\CategoryRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use App\Repository\SubCategoryRepository;
    use Doctrine\Persistence\ManagerRegistry;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
    
    class SecurityController extends AbstractController
    {
      /**
         *
         *
         * @Route("/admin/register", name="admin_register")
         */
    
        public function registerAdmin(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passHasher, $categoryId): Response
        {
            //Cette méthode permet la création d'un compte utilisateur avec des privilèges Administrateur

    
            //Pour enregistrer un compte utilisateur, nous avons besoin de l'Entity Manager
            $entityManager = $doctrine->getManager();
            $user = new User;
            //Nous appele la formulaire  pour l'inscription
            $userForm = $this->createForm(RegistrationType::class, $user);
               
            //Nous récupérons la liste des Catégories pour notre navbar
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
              
                
        // $category = $categoryRepository->findOneBy(['id' => $categoryId]);     
            //Nous traitons les données reçues au sein de notre formulaire
        $userForm->handleRequest($request);
        if($request->isMethod('post') && $userForm->isValid()){
            //On récupère les informations du formulaire
            $data = $userForm->getData();
            //Nous créons et renseignons notre Entity User
            $user = new User;
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $user->setUsername($data['username']);
            $user->setPassword($passHasher->hashPassword($user, $data['password']));
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('app_login'));
        }
        //Si le formulaire n'est pas validé, nous le présentons à l'utilisateur
        return $this->render('indexSecurity/register.html.twig', [
            'categories' => $categories,
            'formName' => 'Inscription Utilisateur',
            'dataForm' => $userForm->createView(),
            // 'category' => $category
        ]);
    }
    
    
    
        /**
         *
         *
         * @Route("/register", name="register")
         */
       
        public function registerUser(Request $request,CategoryRepository $categoryRepository, ManagerRegistry $doctrine, UserPasswordHasherInterface $passHasher): Response

        {
            //Cette méthode permet la création d'un compte Client via formulaire
             //Nous récupérons la liste des Catégories pour notre navbar
            $categories = $categoryRepository->findAll();
            //Pour enregistrer un compte utilisateur, nous avons besoin de l'Entity Manager
            $entityManager = $doctrine->getManager();
            //Nous créons et renseignons notre Utilisateur
            $user = new User;
            //Nous appele la formulaire  pour l'inscription
            $userForm = $this->createForm(RegistrationType::class, $user);
               
            //On applique la Request sur notre formulaire
            $userForm->handleRequest($request);
            //On se prépare à utiliser le formulaire
            if($userForm->isSubmitted() && $userForm->isValid()){
                //On récupère les informations de notre formulaire
                // $data = $userForm->getData();
                
                
                $user->setNickname($userForm->get('nickname')->getData());
                $user->setEmail($userForm->get('email')->getData());
                $user->setAge($userForm->get('age')->getData());
                $user->setRoles(['ROLE_USER']);
                $user->setPassword($passHasher->hashPassword($user, $userForm->get('password')->getData()));
                //On persiste notre Entity
                $entityManager->persist($user);
                $entityManager->flush();
                //Création du flashbag
                $this->addFlash('success', 'Félicitation, vous êtes inscrit, connectez vous à présent');
                //Après le transfert de notre Entity User, on retourne sur le login
                return $this->redirectToRoute('app_login');
            }
            //Si notre formulaire n'est pas validé, nous le présentons à l'Utilisateur
            return $this->render('indexSecurity/register.html.twig', [
                'formName' => 'Inscription Utilisateur',
                'userForm' => $userForm->createView(),
                'categories' => $categories,
            ]);
        }
    
    
    
    
        
         
        /**
         *
         *
         * @Route("/login", name="app_login")
         */
        
        public function login(AuthenticationUtils $authenticationUtils ,CategoryRepository $categoryRepository): Response
        {//la condition nous permet de savoire si un utilisateur est connecté ou pas
            //  if ($this->getUser()) {
            //      return $this->redirectToRoute('target_path');
            //  }
            $categories = $categoryRepository->findAll();
           
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            //  pour récupérer le dernier nom qui été utilisé par l'utilisateur
            $lastUsername = $authenticationUtils->getLastUsername();
    
            return $this->render('indexSecurity/login.html.twig', [
                'last_username' => $lastUsername, 
                'error' => $error,
                'categories'=> $categories,
                
                ]
            );
        }
    
        
    
        /**
         *
         *
         * @Route("/logout", name="app_logout")
         */
        
       //cette méthode peut rester vide ,il est gérer automatiquement par symfony
            public function logout(): void
            {
                throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
            }
    
        }
    
    
    
    

   
