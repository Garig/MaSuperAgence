<?php

namespace App\Controller\Profile;

use App\Repository\PropertyRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/", name="profile.index")
     */
    public function index(PropertyRepository $repository)
    {
        $user = $this->getUser();

        $properties = $repository->findBy(array('sold' => false));
        
        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'properties' => $properties
        ]);
    }

    //     /**
    //  * @Route("/favoris", name="profile.favoris")
    //  */
    // public function favoris(UserRepository $repository)
    // {
    //     $user = $this->getUser();

    //     $properties = $repository->findBy(array('likes' => $user->getLikes()), array('id' => 'ASC'));        
        
    //     return $this->render('profile/favoris.html.twig', [
    //         'user' => $user,
    //         'properties' => $properties
    //     ]);
    // }
}
