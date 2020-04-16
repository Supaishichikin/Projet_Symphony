<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 *
 * @Route("/category")
 */
class
CategoryController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(CategoryRepository $repository)
    {

        $categories = $repository->findBy([], ['id' => 'ASC']);

        return $this->render('Admin/category/index.html.twig', ['categories' => $categories] );
    }


    /**
     * @Route("/edition/{id}", defaults={"id": null}, requirements={"id": "\d+"})
     */
    public function edit(Request $request, EntityManagerInterface $manager, $id)
    {

        $currentUser = $this->getUser();

        if(is_null($id)){
            $category = new Category();
        }else{// modification
            $category = $manager->find(Category::class, $id);

            if(is_null($category)){
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted()){
            if($form->isValid()){

                $manager->persist($category);

                $manager->flush();

                $this->addFlash('success', 'La catégories est enregistrée');

                return $this->redirectToRoute('app_admin_category_index');

            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs' );
            }
        }

        return $this->render(
            'Admin/category/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $currentUser
            ]

        );
    }

    /**
     * @Route("/suppression/{id}", requirements={"id": "\d+"})
     */
    public function delete(EntityManagerInterface $manager, Category $category)
    {

        $manager->remove($category);

        $manager->flush();

        $this->addFlash('success', "La catégorie à été supprimé" );

        return $this->redirectToRoute('app_admin_category_index');
    }
}
