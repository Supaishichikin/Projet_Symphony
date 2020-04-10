<?php

namespace App\Controller;


use App\Entity\Category;
use App\Repository\AchievementRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller
 *
 * @Route("/access/category")
 */
class CategoryController extends AbstractController
{

    public function menu(CategoryRepository $repository)
    {
        $categories = $repository->findBy([], ['id' => 'ASC']);

        return $this->render(
            'category/menu.html.twig',
            [
                'categories' => $categories
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"})
     */
    public function index(AchievementRepository $repository, Category $category)
    {
        $achievements= $repository->findBy(['category' => $category], ['id' => 'ASC']);

        return $this->render(
            'category/index.html.twig',
            [
                'category' => $category,
                'achievements' => $achievements
            ]
        );
    }

}
