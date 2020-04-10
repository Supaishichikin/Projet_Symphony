<?php

namespace App\Controller;

use App\Repository\AchievementRepository;
use App\Repository\CategoryRepository;
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
    public function index(CategoryRepository $repository)
    {
        $categories = $repository->findBy([], ['id' => 'ASC']);

        return $this->render(
            'achievement/index.html.twig',
            [
                'categories' => $categories
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
