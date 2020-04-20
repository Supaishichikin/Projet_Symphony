<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/access/social")
 */
class SocialController extends AbstractController
{
    /**
     * @Route("")
     */
    public function index(Request $request,
                          UserRepository $repository,
                          FriendshipRepository $friendshipRepository,
                          PaginatorInterface $paginator)
    {
        $currentUser = $this->getUser();
        $searchFilter = '';
        $searchUserList = [];
        $getFriends = [];
        $requestingUsers = [];

        /*
         * Génération de la liste d'amis
         */

        $getFriendships1 = $friendshipRepository->findBy(['requestingUser' => $currentUser, 'status' => 'accepted']);
        $getFriendships2 = $friendshipRepository->findBy(['requestedUser' => $currentUser, 'status' => 'accepted']);

        foreach ($getFriendships1 as $friendship) {
            $getFriends[] = $friendship->getRequestedUser();
        }
        foreach ($getFriendships2 as $friendship) {
            $getFriends[] = $friendship->getRequestingUser();
        }

        $friendsList = $paginator->paginate(
            $getFriends,
            $request->query->getInt('page', 1),
            5
        );

        /*
         * Génération de la liste des demandes d'ami (ne fournit que les 5 dernières)
         */

        $getFriendRequests = $friendshipRepository->findBy(['requestedUser' => $currentUser, 'status' => 'waiting'], ['id' => 'DESC'], 5);

        foreach ($getFriendRequests as $friendRequest) {
            $requestingUsers[] = $friendRequest->getRequestingUser();
        }


        /*
         * Recherche parmis les utilisateurs
         */

        if($request->request->has('search_field')) {
            $searchFilter = $request->request->get('search_field');
            if(!empty($searchFilter)) {
                $searchUserList = $repository->search($searchFilter);
                foreach ($searchUserList AS $searchUser) {
                    $searchUser->status = $this->getFriendshipStatus($currentUser, $searchUser, $friendshipRepository);
                }
            }
        }

        return $this->render('social/index.html.twig',
            [
                'searchFilter' => $searchFilter,
                'searchUserList' => $searchUserList,
                'friendsList' => $friendsList,
                'requestingUsers' => $requestingUsers
            ]);
    }

    private function getFriendshipStatus(User $requestingUser, User $requestedUser, FriendshipRepository $repository)
    {
        $friendship1 = $repository->findOneBy(['requestingUser' => $requestingUser, 'requestedUser' => $requestedUser]);
        $friendship2 = $repository->findOneBy(['requestingUser' => $requestedUser, 'requestedUser' => $requestingUser]);

        $status = (object) ['message' => '', 'class' => '', 'disabled' => ''];

        if (is_null($friendship1)) {
            if(is_null($friendship2)) {
                $status->message = "Demander en ami";
                $status->class = "btn-success";
            } else {
                if($friendship2->getStatus() == 'waiting') {
                    $status->message = "Réponse";
                }
                if($friendship2->getStatus() == 'accepted') {
                    $status->message = "Retirer de la liste d'amis";
                    $status->class = "btn-danger";
                }
            }
        } else {
            if($friendship1->getStatus() == 'waiting') {
                $status->message = "En attente";
                $status->class = "btn-secondary";
                $status->disabled = 'disabled';
            }
            if($friendship1->getStatus() == 'accepted') {
                $status->message = "Retirer de la liste d'amis";
                $status->class = "btn-danger";
            }
        }

        return $status;
    }

    /**
     * @Route("/process/{id}/{choice}", defaults={"choice" : null}, requirements={"id", "\d+"})
     */
    public function processFriendship($id,
                                      $choice,
                                      UserRepository $userRepository,
                                      FriendshipRepository $friendshipRepository,
                                      EntityManagerInterface $manager)
    {
        $currentUser = $this->getUser();
        $otherUser = $userRepository->find($id);
        if (!is_null($otherUser)) {
            $status = $this->getFriendshipStatus($currentUser, $otherUser, $friendshipRepository);
            switch($status->message) {
                case('Demander en ami'):
                    $newFriendship = new Friendship();
                    $newFriendship->setRequestingUser($currentUser);
                    $newFriendship->setRequestedUser($otherUser);
                    $newFriendship->setStatus('waiting');
                    $manager->persist($newFriendship);
                    $manager->flush();
                    break;
                case('Réponse'):
                    $currentFriendship = $friendshipRepository
                        ->findOneBy(['requestingUser' => $otherUser, 'requestedUser' => $currentUser]);
                    if($choice == 'accept'){
                        $currentFriendship->setStatus('accepted');
                        $manager->persist($currentFriendship);
                    } else {
                        if($choice == 'refuse') {
                            $manager->remove($currentFriendship);
                        } else {
                            $this->addFlash('error', 'Une erreur est survenue1');
                            return $this->redirectToRoute('app_social_index');
                        }
                    }
                    $manager->flush();
                    break;
                case("Retirer de la liste d'amis"):
                    $currentFriendship = $friendshipRepository
                        ->findOneBy(['requestingUser' => $otherUser, 'requestedUser' => $currentUser]);
                    if(is_null($currentFriendship)) {
                        $currentFriendship = $friendshipRepository
                            ->findOneBy(['requestingUser' => $currentUser, 'requestedUser' => $otherUser]);
                    }
                    $manager->remove($currentFriendship);
                    $manager->flush();
                    break;
                default:
                    $this->addFlash('error', 'Une erreur est survenue2');
            }
        } else {
            $this->addFlash('error', 'Une erreur est survenue3');
        }

        return $this->redirectToRoute('app_social_index');
    }
}
