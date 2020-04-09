<?php

namespace App\Controller;

use App\Form\EditEmailType;
use App\Form\EditPseudoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
    public function index(Request $request, EntityManagerInterface $manager)
    {
        $currentUser = $this->getUser();
        $emailForm = $this->createForm(EditEmailType::class, $currentUser);
        $pseudoForm = $this->createForm(EditPseudoType::class, $currentUser);

        $this->editField($emailForm, $request, $manager, $currentUser);
        $this->editField($pseudoForm, $request, $manager, $currentUser);


        return $this->render('settings/index.html.twig', [
            'user' => $currentUser,
            'emailForm' => $emailForm->createView(),
            'pseudoForm' => $pseudoForm->createView()
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
