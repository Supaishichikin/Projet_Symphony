<?php

namespace App\Controller;

use App\Entity\Achievement;
use App\Entity\User;
use App\Entity\UserAchievement;
use App\Form\AchievementType;
use App\Form\SearchAchievementsType;
use App\Repository\AchievementRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\UserAchievementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("access/achievement")
 */
class AchievementController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(AchievementRepository $repository,
                          PaginatorInterface $paginator,
                          Request $request)
    {

        $form = $this->createForm(SearchAchievementsType::class);
        $form->handleRequest($request);

        dump($request->query);

        $donnees = $repository->search((array) $form->getData());
        dump($donnees);
        $limit = count($donnees);
        dump($limit);
        $achievements = $paginator->paginate(
            $donnees,
            $request->query->getInt(
                'page', 1),
            6
        );
        return $this->render(
            'achievement/index.html.twig',
            [
                'achievements' => $achievements,
                'form' => $form->createView()
            ]
        );
    }

    public function card(UserAchievementRepository $linkRepository, Achievement $achievement)
    {

        $achievement->status = $this->getStatus($achievement, $this->getUser(), $linkRepository);

        return $this->render('achievement/card.html.twig',
            [
                'achievement' => $achievement
            ]);
    }

    /**
     * @Route("/detail/{id}", requirements={"id" : "\d+"})
     */
    public function detail(AchievementRepository $repository,UserAchievementRepository $linkRepository, $id)
    {
        $achievement = $repository->findOneBy(['id' => $id]);

        $status = $this->getStatus($achievement, $this->getUser(), $linkRepository);

        return $this->render(
            'achievement/detail.html.twig',
            [
                'achievement' => $achievement,
                'status' => $status
            ]
        );
    }


    private function getStatus(Achievement $achievement, User $user, UserAchievementRepository $linkRepository)
    {
        $link = $linkRepository->findOneBy(['user' => $user, 'achievement' => $achievement]);

        $status = (object) ['message' => '', 'class' => ''];

        if (is_null($link)) {
            $status->message = "Commencer l'activité";
            $status->class = "btn-info";
        } else {
            if(is_null($link->getEndDate())){
                $status->message = "Terminer l'activité";
                $status->class = "btn-success";
            } else {
                $status->message = "Recommencer l'activité";
                $status->class = "btn-warning";
            }
        }

        return $status;
    }

    /**
     * @Route("/process/{id}", requirements={"id" : "\d+"})
     */
    public function processAchievement(Achievement $achievement,
                                       UserAchievementRepository $linkRepository,
                                       EntityManagerInterface $manager)
    {
        if(is_null($achievement)){
            $this->addFlash('error', "Cette activité n'a pas été trouvée");
            return $this->redirectToRoute('app_achievement_index');
        }

        $user = $this->getUser();
        $link = $linkRepository->findOneBy(['user' => $user, 'achievement' => $achievement]);

        if (is_null($link)) {
            $newlink = new UserAchievement();
            $newlink->setUser($user);
            $newlink->setAchievement($achievement);
            $newlink->setStartDate(new \DateTime());
            $newlink->setEndDate(null);

            $manager->persist($newlink);
        } else {
            if(is_null($link->getEndDate())){
                $link->setEndDate(new \DateTime());
            } else {
                $link->setStartDate(new \DateTime());
                $link->setEndDate(null);
            }
            $manager->persist($link);
        }

        $manager->flush();

        return $this->redirectToRoute('app_achievement_index');
    }
}
