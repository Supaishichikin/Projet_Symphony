<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
                          EntityManagerInterface $manager)
    {
        /*
         * Inscription
         */
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user, ["validation_groups" => ["Default", "Registration"]]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) {
                $encodedPassword = $passwordEncoder->encodePassword(
                    $user,
                    $user->getPlainPassword()
                );

                $user->setPassword($encodedPassword);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Votre compte a bien été créé');

                return $this->redirectToRoute('app_index_index');
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
     * @Route("/login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        /*
         * Connexion
         */

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
//         Fait la connexion ET récupère une potentielle erreur
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        if(!empty($error)) {
            $this->addFlash('error', 'Identifiants incorrects');
        }

        return $this->render('index/index.html.twig',
            [
                'form' => $form->createView(),
                'last_username' => $lastUsername
            ]);
    }

    /**
     * @Route("/deconnexion")
     */
    public function logout()
    {

    }
}
