<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Twig\Environment;
use App\Notification\MotPasseNotification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
// use Swiftmailer\Swiftmailer\lib\classes\Swift\Plugins\Loggers\ArrayLogger;
use Symfony\Component\Translation\TranslatorInterface;


class SecurityController extends AbstractController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(\Swift_Mailer $mailer, Environment $renderer, UserPasswordEncoderInterface $encoder, ObjectManager $em)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * @Route( "/{_locale}/connexion", name="connexion")
     * requirements:
     *      _locale: fr|en
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastusername = $authenticationUtils->getLastUsername();
        return $this->render('security/login/login.html.twig',[
            'last_username' => $lastusername,
            'error'         => $error
        ]);
    }

    /**
     * @Route( "/{_locale}/register", name="inscription")
     * requirements:
     *      _locale: fr|en
     */
    public function register(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator, Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', $translator->trans('register.message.success'));

            return $this->redirectToRoute('connexion');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/registration/registration.html.twig', [
            'form'  => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route( "/{_locale}/deconnexion", name="deconnexion")
     * requirements:
     *     _locale: fr|en
     */
    public function logout() {}

        /**
     * @Route("/{_locale}/forgotten_password", name="app_forgotten_password")
     * requirements:
     *      _locale: fr|en
     */
    public function forgottenPassword( Request $request, TokenGeneratorInterface $tokenGenerator, MotPasseNotification $notification): Response
    {

        if ($request->isMethod('POST')) {

            $email = $request->request->get('_username');
            //var_dump($email);die();
            //la c'est ok je récupère l'email. Fallait bien mettre le "name" de l'input
            $user = $this->em->getRepository(User::class)->findOneByEmail($email);
            //var_dump($user);die();
            //la c'est ok je récupère le user complet. La méthode findOneByEmail($email) signifie bien qqch pour Doctrine.
            //Pas besoin de la créé dans le repository

            if ($user === null) {
                $this->addFlash('danger', 'Email inconnu');
                return $this->redirectToRoute('app_forgotten_password');
            }
            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app_forgotten_password');
            }

            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            // $notification->notify($user, $url);

            // $logger = new Swift_Plugins_Loggers_ArrayLogger();
            // $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

            $message = (new \Swift_Message('Mot de passe oublié'))
            ->setFrom(['noreply@alexandrehouriez.fr'=> 'Mon Agence'])
            ->setTo($user->getEmail())
            ->setBody($this->renderer->render('security/resetPassword/emails/token.html.twig', [
                'user' => $user,
                'url' => $url
            ]), 'text/html')
            ->SetCharset('utf-8');

            try {
            $this->mailer->send($message);
            $this->addFlash('success', 'Email envoyé');
            } catch (\Swift_TransportException $e) {
                $this->addFlash('warning', $e->getMessage());
            } catch (\Exception $e){
                $this->addFlash('warning', $e->getMessage());
            }

            return $this->redirectToRoute('home');
        }

        return $this->render('security/resetPassword/forgotten_password.html.twig');
    }

        /**
     * @Route("/{_locale}/reset_password/{token}", name="app_reset_password")
     * requirements:
     *      _locale: fr|en
     */
    public function resetPassword( AuthenticationUtils $authenticationUtils, Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {

        if ($request->isMethod('POST')) {

            $user = $this->em->getRepository(User::class)->findOneByResetToken($token);

            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('home');
            }

            if ($request->request->get('password') !== $request->request->get('confirm_password')){

                $this->addFlash('danger', 'Les mots de passes ne sont pas identiques');
    
                return $this->redirectToRoute('app_reset_password', [
                    'token' => $token,
                ]);

            }elseif (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$#", $request->request->get('password'))) {

                $this->addFlash('danger', '8 caractères minimum avec au moins une majuscule un chiffre et un caractère spécial');
    
                return $this->redirectToRoute('app_reset_password', [
                    'token'   => $token,
                ]);

            }else{
                $user->setResetToken(null);
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
                $this->em->flush();
    
                $this->addFlash('success', 'Mot de passe mis à jour');
    
                return $this->redirectToRoute('connexion');
            }

        } else {
            $error = $authenticationUtils->getLastAuthenticationError();

            return $this->render('security/resetPassword/reset_password.html.twig', [
                'token' => $token,
                'error' => $error
            ]);
        }

    }

    /**
     * @Route("/{_locale}/profile/edit_password", name="edit_password")
     * requirements:
     *     _locale: fr|en
     */
    public function editPassword(AuthenticationUtils $authenticationUtils, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();
        if ($request->isMethod('POST')) {
        // var_dump($request->request->get('password'));die();

            

            // var_dump($passwordEncoder->encodePassword($user, $request->request->get('password')));
            // echo '<br>';
            // var_dump($user->getPassword());die();
            
            //la méthode isPasswordValid elle te dit si le mot de passe bcrypté il est correct c est super pratique
            if (!$passwordEncoder->isPasswordValid($user, $request->request->get('password'))){

                $this->addFlash('danger', 'Le mot de passe actuel n\'est pas valide');
                return $this->redirectToRoute('edit_password');
    
            }elseif (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$#", $request->request->get('new_password'))) {

                $this->addFlash('danger', '8 caractères minimum avec au moins une majuscule un chiffre et un caractère spécial');
                return $this->redirectToRoute('edit_password');
        
            }elseif ($request->request->get('new_password') !== $request->request->get('confirm_password')){

                $this->addFlash('danger', 'Les mots de passes ne sont pas identiques');
                return $this->redirectToRoute('edit_password');

            }else{
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('new_password')));
                $this->em->flush();
            }

            $this->addFlash('success', 'Mot de passe modifié !');
            return $this->redirectToRoute('profile.index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();


        return $this->render('security/editPassword/edit_password.html.twig', [
            'error' => $error
        ]);
    }

        /**
     * @Route("/{_locale}/profile/edit_infos", name="edit_infos")
     * requirements:
     *     _locale: fr|en
     */

    public function editInfos(AuthenticationUtils $authenticationUtils, Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $repository)
    {
        $user = $this->getUser();
        if ($request->isMethod('POST')) {
            // var_dump($request->request->get('password'));die();
            // var_dump($passwordEncoder->encodePassword($user, $request->request->get('password')));
            // var_dump($user->getPassword());die();
            $email = $request->request->get('email');
            $base_email = $repository->findOneByEmail($email);
            $username = $request->request->get('username');
            $base_username = $repository->findOneByUsername($username);
            //la méthode isPasswordValid elle te dit si le mot de passe bcrypté il est correct c est super pratique
            if (!$passwordEncoder->isPasswordValid($user, $request->request->get('password'))){

                $this->addFlash('danger', 'Le mot de passe n\'est pas correct');
                return $this->redirectToRoute('edit_infos');

            }elseif (!empty($base_email) && !empty($base_username)){

                if ($email === $this->getUser()->getEmail() && $username === $this->getUser()->getUsername()){
                    $user->setUsername($request->request->get('username'));
                    $user->setEmail($request->request->get('email'));
                    $this->em->flush();
                    $this->addFlash('success', 'Informations du profil modifiées !');
                    return $this->redirectToRoute('profile.index');
                }else{
                    $this->addFlash('danger', 'Le username et/ou l\'email existe déja');
                    return $this->redirectToRoute('edit_infos');
                }

            }elseif (empty($base_email) && empty($base_username)) {

                    $user->setUsername($request->request->get('username'));
                    $user->setEmail($request->request->get('email'));
                    $this->em->flush();
                    $this->addFlash('success', 'Informations du profil modifiées !');
                    return $this->redirectToRoute('profile.index');

            }elseif (!empty($base_email) && empty($base_username)) {

                
                if ($email === $this->getUser()->getEmail() && empty($base_username)){
                    $user->setUsername($request->request->get('username'));                    
                    $this->em->flush();
                    $this->addFlash('success', 'Informations du profil modifiées !');
                    return $this->redirectToRoute('profile.index');
                }else{
                    $this->addFlash('danger', 'Le username existe déja');
                    return $this->redirectToRoute('edit_infos');
                }
        
            }elseif (!empty($base_username) && empty($base_email)) {

                if ($username === $this->getUser()->getUsername() && empty($base_email)){
                    $user->setEmail($request->request->get('email'));
                    $this->em->flush();
                    $this->addFlash('success', 'Informations du profil modifiées !');
                    return $this->redirectToRoute('profile.index');
                }else{
                    $this->addFlash('danger', 'L email existe déja');
                    return $this->redirectToRoute('edit_infos');
                }
        
            }else{
                return new Response('exception');
            }

        }

        $error = $authenticationUtils->getLastAuthenticationError();


        return $this->render('security/editInfos/edit_infos.html.twig', [
            'error' => $error,
            'user' => $user
        ]);
    }

    /**
     * @Route("/{_locale}/profile/delete_profile-{id}", name="delete_profile", methods="DELETE")
     * requirements:
     *     _locale: fr|en
     */
    public function deleteProfile(User $user, Request $request, TokenStorageInterface $tokenStorage, Session $session): response
    {

        if ($this->isCsrfTokenValid('delete' . $user->getId() , $request->get('_token'))){
            $this->em->remove($user);
            $this->em->flush();
            // $user->setResetToken(null);
            // $user->serialize();
            //     $session = $this->get('session');
            // $session = new Session();
            // $session->invalidate();
            $tokenStorage->setToken(null);
            $session->invalidate();
        }
        $this->addFlash('success', 'Profil supprimé');
        return $this->redirectToRoute('home');
    }

}