<?php

namespace App\Controller;

use App\Repository\AchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("access/achievement")
 */
class AchievementController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(AchievementRepository $repository)
    {
        $achievements= $repository->findBy([], ['id' => 'ASC']);

        return $this->render(
            'achievement/index.html.twig',
            [
                'achievements' => $achievements
            ]
        );
    }

    /**
     * @Route("/detail/{id}")
     */
    public function detail(AchievementRepository $repository, $id)
    {
        $achievement = $repository->findOneBy(['id' => $id]);

        return $this->render(
            'achievement/detail.html.twig',
            [
                'achievement' => $achievement
            ]
        );
    }
}
