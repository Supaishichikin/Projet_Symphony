<?php

namespace App\Controller;

use App\Repository\UserAchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index(UserAchievementRepository $repository)
    {
        $achievementsInProgress = [];
        $completedAchievements = [];
        $startedUserAchievements = $repository->findBy(['user' => $this->getUser()]);
        foreach ($startedUserAchievements as $userAchievement) {
            if (is_null($userAchievement->getEndDate())) {
                $achievementsInProgress[] = $userAchievement->getAchievement();
            } else {
                $completedAchievements[] = $userAchievement->getAchievement();
            }
        }

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'in_progress' => $achievementsInProgress,
            'completed' => $completedAchievements
        ]);
    }
}
