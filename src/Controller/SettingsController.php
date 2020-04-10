<?php

namespace App\Controller;

use App\Form\EditEmailType;
use App\Form\EditPasswordType;
use App\Form\EditPseudoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class SettingsController
 * @package App\Controller
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(Request $request,
                          EntityManagerInterface $manager,
                          UserPasswordEncoderInterface $passwordEncoder)
    {
        $currentUser = $this->getUser();
        $emailForm = $this->createForm(EditEmailType::class, $currentUser);
        $pseudoForm = $this->createForm(EditPseudoType::class, $currentUser);

        $this->editField($emailForm, $request, $manager, $currentUser);
        $this->editField($pseudoForm, $request, $manager, $currentUser);

        $passwordForm = $this->createForm(EditPasswordType::class, $currentUser);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted()){
            if($passwordForm->isValid()){
                $currentPassword = $request->request->get('currentPassword');
                if ($passwordEncoder->isPasswordValid($currentUser, $currentPassword)) {
                    $newEncodedPassword = $passwordEncoder->encodePassword(
                        $currentUser,
                        $currentUser->getPlainPassword()
                    );

                    $currentUser->setPassword($newEncodedPassword);
                    $manager->persist($currentUser);
                    $manager->flush();
                }
            } else {
                $this->addFlash('error', "Le champ n'est pas valide");
            }
        }


        return $this->render('settings/index.html.twig', [
            'user' => $currentUser,
            'emailForm' => $emailForm->createView(),
            'pseudoForm' => $pseudoForm->createView(),
            'passwordForm' => $passwordForm->createView()
        ]);
    }

    /**
     * @Route("/delete_account")
     */
    public function deleteAccount(Request $request,
                                  EntityManagerInterface $manager,
                                  UserPasswordEncoderInterface $passwordEncoder,
                                  SessionInterface $session)
    {
        $currentUser = $this->getUser();
        $plainPassword = $request->request->get('checkPassword');
        if (!is_null($plainPassword)){
            if ($passwordEncoder->isPasswordValid($currentUser, $plainPassword)) {

                $manager->remove($currentUser);
                $manager->flush();

                $this->get('security.token_storage')->setToken(null);

                $this->addFlash('success', 'Votre compte a bien été supprimé');
                return $this->redirectToRoute('app_index_index');
            } else {
                $this->addFlash('error', 'Mot de passe incorrect');
            }
        }

        return $this->render('settings/deleteAccount.html.twig',
            [
                'user' => $currentUser
            ]);
    }

    private function editField(FormInterface $form, Request $request, EntityManagerInterface $manager, UserInterface $user)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', "Le champ a bien été modifié");
            } else {
                $this->addFlash('error', "Le champ n'est pas valide");
            }
        }
    }
}
