<?php

namespace App\Controller;

use App\Form\ResetPassRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('app_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/oubli-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        TokenGeneratorInterface $tokenGeneratorInterface,
        EntityManagerInterface $entityManagerInterface,
        UsersRepository $usersRepository,
        MailerInterface $mailer
    ): Response {
        //!pour afficher le formulaire crée depuis le maker, il faut que le controller l'appel !
        //* la méthode createForm() vient de abstractController
        $form = $this->createForm(ResetPassRequestType::class);

        //!pour récupérer le $_POST lors de la soumission. heandleRequest nous dis "traite le formulaire"
        // en récupérant la requete via $requeste. Mais pour ça il faut l'injection de dépendance "Request $request";
        //si pas de données post, cette ligne ne fait rien du tout
        $form->handleRequest($request);

        //! On vérifie bien si le formulaire est "soumis" et s'il est valide pour entrer dans le if et traiter les données
        if ($form->isSubmitted() && $form->isValid()) {
            //on va chercher le user par son email. Il faut donc le repository ! et encore grace à l'injection de dépendance
            // Avant on récupère le mail du formulaire :
            $emailForm = $form->get('email')->getData();
            // et on fait appel au model pour trouver le mail et donc savoir si ce mail existe !
            $user = $usersRepository->findOneByEmail($emailForm); //$user est aussi une instance de Users (getter/setter)

            //on vérifie ici l'existence du User par l'email reçu en POST
            if ($user) {
                //on va générer un token de réinitialisation via un service de symfony
                $token = $tokenGeneratorInterface->generateToken();
                // va utiliser $user pour "setter" la colonne token de l'entité
                $user->setToken($token);
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();

                //on génère un lien de reset du mot de passe grace au abstractController : generateUrl()
                /**
                 * paramètre 1 la route
                 * paramètre 2 la variable get
                 * paramètre 3 le chemin absolu
                 * resultat : "https://127.0.0.1:8000/oubli-pass/e2o4ghMbRUHOoAnWegBjIE1HFsZrLFgeEqi8Ukrv-aQ"
                 */
                $url = $this->generateUrl('app_ResetPass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                //on créer les données que va contenir le mail
                $context = compact('url', 'user');
                //on envoi le mail, il contiendra un lien avec le token, qui sera vérifié dans la route "app_ResetPass"
                //reste du code à compléter :
                $email = (new TemplatedEmail())
                    ->from('noreply@example.com')
                    ->to(new Address($user->getEmail()))
                    ->subject('Demande de réinitialisation de mot de passe')
                    ->htmlTemplate('emailsTemplates/resetPassword.html.twig')
                    ->context($context);

                $mailer->send($email);

                //fin de l'ajout

                //message de succès
                $this->addFlash('successMessageFlash', "Un email vient d'être envoyé contenant un lien de réinitialisation.");

                return $this->redirectToRoute('app_home');
            }

            //user est null
            $this->addFlash('danger', 'Email inconnue');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/resetPassRequest.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route(path: '/reinitialisation-pass/{token}', name: 'app_ResetPass')]
    public function resetPass(
        string $token,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        UsersRepository $userRepository
    ){

        //on vérifie si le token du get est présent en bdd

        //findOneBy : méthode magique. Il suffit de concaténer le nom de l'entité

        $user = $userRepository->findOneByToken($token);
        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // On efface le token
                $user->setToken(null);
                $user->setPassword(
                    $userPasswordHasherInterface->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();

                $this->addFlash('successMessageFlash', "Mot de passe changé avec succès, connectez-vous !");
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/resetPass.html.twig', [
                'passForm' => $form->createView()
            ]);
        }
        $this->addFlash('danger', 'jeton invalid');
        return $this->redirectToRoute('app_login');
    }
}
