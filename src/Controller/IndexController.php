<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use RandomLib\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class IndexController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(Request $request,
                          UserPasswordEncoderInterface $passwordEncoder,
                          EntityManagerInterface $manager,
                          UserRepository $repository)
    {
        if(!is_null($this->getUser())){
            return $this->redirectToRoute("app_achievement_index");
        }

        /*
         * Inscription
         */
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user, ["validation_groups" => ["Default", "Registration"]]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) {
                $error = false;
                if ($repository->findOneBy(['email' => $user->getEmail()])) {
                    $this->addFlash('error', 'Cette adresse email est déjà utilisée');
                    $error = true;
                }

                if($repository->findOneBy(['pseudo' => $user->getPseudo()])) {
                    $this->addFlash('error', 'Ce pseudo est déjà utilisé');
                    $error = true;
                }

                if(!$error) {
                    $encodedPassword = $passwordEncoder->encodePassword(
                        $user,
                        $user->getPlainPassword()
                    );

                    $user->setPassword($encodedPassword);

                    $manager->persist($user);
                    $manager->flush();

                    $this->addFlash('success', 'Votre compte a bien été créé');

                    return $this->redirectToRoute('app_index_index');
                }
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render('index/index.html.twig',
            [
                'form' => $form->createView(),
                'last_username' => ''
            ]);
    }

    /**
     * @Route("/available/{pseudo}")
     */
    public function isUsernameAvailable(UserRepository $repository, string $pseudo) {
        if(is_null($repository->findOneBy(['pseudo' => $pseudo]))) {
            return new Response("Pseudo disponible");
        } else {
            return new Response("Pseudo déjà utilisé");
        }
    }

    /**
     * @Route("/login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
//         Fait la connexion ET récupère une potentielle erreur
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        if(!empty($error)) {
            $this->addFlash('error', 'Identifiants incorrects');
            return $this->render('index/index.html.twig',
                [
                    'form' => $form->createView(),
                    'last_username' => $lastUsername
                ]);
        } else {
            return $this->redirectToRoute("app_achievement_index");
        }
    }

    /**
     * @Route("/logout")
     */
    public function logout()
    {

    }

    /**
     * @Route("/forgotten_password")
     */
    public function forgottenPassword(Request $request,
                                      MailerInterface $mailer,
                                      UserRepository $repository,
                                      UserPasswordEncoderInterface $passwordEncoder,
                                      EntityManagerInterface $manager)
    {
        if ($request->request->has('emailForReset')) {
            $emailForReset = $request->request->get('emailForReset');
            $user = $repository->findOneBy(['email' => $emailForReset]);

            if(!is_null($user)) {
                $newPassword = $this->generateTemporaryPassword();

                $newEncodedPassword = $passwordEncoder->encodePassword(
                    $user,
                    $newPassword
                );

                $user->setPassword($newEncodedPassword);
                $manager->persist($user);
                $manager->flush();

                $mail = new Email();

                $mailBody = $this->renderView('index/resetPassword_body.html.twig',
                    [
                        'password' => $newPassword
                    ]);

                $mail
                    ->subject('Mot de passe temporaire')
                    ->from('daviet.charles@gmail.com')
                    ->to($emailForReset)
                    ->replyTo($emailForReset)
                    ->html($mailBody);

                $mailer->send($mail);

                return $this->redirectToRoute('app_index_index');
            } else {
                $this->addFlash('error', "L'adresse email fournie n'est associé à aucun compte");
            }


        }

        return $this->render('index/forgottenPassword.html.twig');
    }

    private function generateTemporaryPassword()
    {
        $factory = new Factory();
        $generator = $factory->getMediumStrengthGenerator();
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ#?!&-_+";
        return $generator->generateString(20, $characters);
    }
}
