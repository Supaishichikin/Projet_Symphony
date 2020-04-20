<?php

namespace App\Controller\Admin;

use App\Entity\Achievement;
use App\Form\AchievementType;
use App\Repository\AchievementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function
    edit(Request $request, EntityManagerInterface $manager,AchievementRepository $repository,
                         PaginatorInterface $paginator, $id)
    {

        $donnees = $repository->findBy([], ['id' => 'ASC']);
        $achievements = $paginator->paginate(
            $donnees,
            $request->query->getInt(
                'page', 1),
            3
        );

        $currentUser = $this->getUser();

        $originalImage = null;

        if(is_null($id)) { // création
            $achievement = new Achievement();
        } else {
            $achievement = $manager->find(Achievement::class, $id);

            if(is_null($achievement)){
                throw new NotFoundHttpException();
            }

            if(!is_null($achievement->getImage())){
                $originalImage = $achievement->getImage();
                $achievement->setImage(
                  new File($this->getParameter('upload_dir') . '/' . $originalImage)
                );
            }
        }

        $form = $this->createForm(AchievementType::class, $achievement);
        $form->handleRequest($request);
        // dump($achievement);
        if($form->isSubmitted()){
            if($form->isValid()) {
                /** @var UploadedFile|null $image */
                $image = $achievement->getImage();

                if(!is_null($image)) {
                    $filename = uniqid() . '.' . $image->guessExtension();

                    $image->move(
                        $this->getParameter('upload_dir'),
                        $filename
                    );
                    $achievement->setImage($filename);

                    if(!is_null($originalImage)) {
                        unlink($this->getParameter('upload_dir') . '/' . $originalImage);
                    }
                } else {
                    $achievement->setImage($originalImage);
                }

                $manager->persist($achievement);
                $manager->flush();

                $this->addFlash('success', "L'activité est enregistrée");

                return $this->redirectToRoute('app_admin_achievement_index');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }


        return $this->render(
            'Admin/achievement/edit.html.twig',
            [
                'form' => $form->createView(),
                'achievements' => $achievements,
                'user' => $currentUser
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
