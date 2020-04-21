<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ContactType;
use App\Form\RegistrationType;
use App\Repository\AchievementRepository;
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
                          UserRepository $repository,
                          AchievementRepository $achievementRepository)
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

        $randomAchievements = $this->getRandomAchievements($achievementRepository, 6);

        return $this->render('index/index.html.twig',
            [
                'form' => $form->createView(),
                'last_username' => '',
                'achievements' => $randomAchievements
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

    private function getRandomAchievements(AchievementRepository $repository, int $amount)
    {
        $allAchievements = $repository->findAll();
        $selectedAchievements = [];

        $index = array_rand($allAchievements, $amount);
        foreach ($index as $i) {
            $selectedAchievements[] = $allAchievements[$i];
        }

        return $selectedAchievements;
    }

    /**
     * @Route("/login")
     */
    public function login(AuthenticationUtils $authenticationUtils, AchievementRepository $achievementRepository)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if(!empty($error)) {
            $this->addFlash('error', 'Identifiants incorrects');
            return $this->redirectToRoute("app_index_index");
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
        if(!is_null($this->getUser())) {
            return $this->redirectToRoute('app_achievement_index');
        }

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

    /**
     * @Route("/contact")
     */
    public function contact(Request $request, MailerInterface $mailer)
    {
        $form = $this->createForm(ContactType::class);

        if(!is_null($this->getUser())){
            $form->get('name')->setData($this->getUser());
            $form->get('email')->setData($this->getUser()->getEmail());
        }

        $form->handleRequest($request);

        if($form->isSubmitted()){
            if($form->isValid()){
                $data = $form->getData();

                $mail = new Email();

                $mailBody = $this->renderView(
                    'contact/contact_body.html.twig',
                    [
                        'data' => $data
                    ]
                );

                $mail
                    ->subject('Nouveau message sur le site')
                    ->from('daviet.charles@gmail.com')
                    ->to('cdaviet@protonmail.com')
                    ->replyTo($data['email'])
                    ->html($mailBody)
                ;

                $mailer->send($mail);
                $this->addFlash('success', 'Votre message à été envoyé');
            }else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render(
            'contact/index.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/confidentialite")
     */
    public function confidentialite()
    {
        return $this->render('confidentialite/index.html.twig');
    }

    /**
     * @Route("/condition")
     */
    public function condition()
    {
        return $this->render('condition/index.html.twig');
    }
}
