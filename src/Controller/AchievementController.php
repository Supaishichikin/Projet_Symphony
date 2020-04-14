<?php

namespace App\Controller;

use App\Repository\AchievementRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(CategoryRepository $repository, AchievementRepository $repository2, PaginatorInterface $paginator, Request $request)
    {
        $categories = $repository->findBy([], ['id' => 'ASC']);

        $donnes = $repository2->findBy([],['id' => 'ASC']);

        $achievements = $paginator->paginate(
            $donnes,
            $request->query->getInt(
                'page', 1),
            6
        );

        return $this->render(
            'achievement/index.html.twig',
            [
                'categories' => $categories,
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
