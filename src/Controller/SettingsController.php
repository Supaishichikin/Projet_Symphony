<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingsController
 * @package App\Controller
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        $currentUser = $this->getUser();
        return $this->render('settings/index.html.twig', [
            'user' => $currentUser
        ]);
    }
}
