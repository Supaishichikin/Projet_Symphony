<?php

namespace App\Controller\Admin;

use App\Entity\Achievement;
use App\Form\AchievementType;
use App\Repository\AchievementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AchievementController
 * @package App\Controller\Admin
 *
 * @Route("/achievement")
 */
class AchievementController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(AchievementRepository $repository)
    {
        $achievements = $repository->findBy([], ['id' => 'ASC']);

        return $this->render(
        'Admin/achievement/index.html.twig',
            [
                'achievements' => $achievements
            ]
        );
    }

    /**
     *  @Route("/edit/{id}", defaults={"id": null}, requirements={"id": "\d+"})
     */
    public function edit(Request $request, EntityManagerInterface $manager, $id)
    {

        if(is_null($id)) { // création
            $achievement = new Achievement();
        } else {
            $achievement = $manager->find(Achievement::class, $id);

            if(is_null($achievement)){
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(AchievementType::class, $achievement);
        $form->handleRequest($request);
        // dump($achievement);
        if($form->isSubmitted()){
            if($form->isValid()) {
                $manager->persist($achievement);
                $manager->flush();

                $this->addFlash('success', "Le challenge est enregistré");

                return $this->redirectToRoute('app_admin_achievement_index');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }


        return $this->render(
            'Admin/achievement/edit.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/suppression/{id}", requirements={"id": "\d+"})
     */
    public function delte(EntityManagerInterface $manager, Achievement $achievement)
    {
        $manager->remove($achievement);
        $manager->flush();

        $this->addFlash('success', "Le challenge est supprimé");

        return $this->redirectToRoute('app_admin_achievement_index');
    }
}
