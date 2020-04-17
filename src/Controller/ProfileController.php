<?php

namespace App\Controller;

use App\Repository\UserAchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProfileController
 * @package App\Controller
 *
 * @Route("/access/profile")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(UserAchievementRepository $repository)
    {
        $achievementsInProgress = [];
        $completedAchievements = [];
        $startedUserAchievements = $repository->findBy(['user' => $this->getUser()]);
        foreach ($startedUserAchievements as $userAchievement) {
            $currentAchievement = $userAchievement->getAchievement();
            if (is_null($userAchievement->getEndDate())) {
                $achievementsInProgress[] = $currentAchievement;
                if($userAchievement->getTimescompleted() > 0) {
                    $completedAchievements[] = $currentAchievement;
                }
            } else {
                $completedAchievements[] = $currentAchievement;
            }
        }

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'in_progress' => $achievementsInProgress,
            'completed' => $completedAchievements
        ]);
    }
}
