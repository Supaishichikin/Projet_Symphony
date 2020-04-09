<?php

namespace App\Controller;


use App\Repository\AchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AchievementController extends AbstractController
{
    /**
     * @Route("/achievement")
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
}
