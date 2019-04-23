<?php

namespace App\Controller;

use App\Entity\Property;
use App\Entity\PropertyLike;
use App\Repository\PropertyRepository;
use App\Repository\PropertyLikeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    //la tu remarquera on a pas declaré le $repository dans __construct et ça marche quand meme sinon on aurait mis
    //$properties = $this->repository->findBy() et on aurait pas eu a typehinté la méthode index()
    /**
     * @Route("/", name="home")
     */
    public function index(PropertyRepository $repository): Response
    {
        // Normalement j'utilise findLatest() mais on peut s'en passer quand ya qu'un seul critère
        $properties = $repository->findBy(array('sold' => false), array('id' => 'ASC'), 3, 0);
        // dump($properties);
        return $this->render('pages/home.html.twig', [
            'properties' => $properties
        ]);
    }

    /**
     * @Route("/salut", name="salut")
     */
    public function salut(): Response
    {
        return new Response('Salut les gens !');
    }

    /**
     * @Route("/property/{id}/like", name="property_like")
     * 
     * Permet de liker ou unliker un article
     *
     * @param property $property
     * @param ObjectManager $manager
     * @param propertyLikeRepository $likeRepo
     * @return Response
     */
    public function like(Property $property, ObjectManager $manager, PropertyLikeRepository $likeRepo) : Response
    {
        $user = $this->getUser();

        //3 cas
        //le 1er cas si l'utilisateur n'est pas connecté
        if (!$user) return $this->json([
            'code'    => 403,
            'message' => 'Unauthorized'
        ], 403);

        //et ici ya 2 cas:
        //   - est ce que ce property est liké, il faut supprimer le like
        //   - est ce que ce property n est pas liké, il faut créer un like
        if($property->isLikedByUser($user)){
            $like = $likeRepo->findOneBy([
                'property' => $property,
                'user'     => $user
            ]);

            $manager->remove($like);
            $manager->flush();

            return $this->json([
                'code'    => 200, //attention ce code ici il sert strictement à rien, c'est le code en dessous qui est obligatoire
                'message' => 'Like bien supprimé',
                'likes'   => $likeRepo->count(['property' => $property])
            ], 200);
        }

        $like = new PropertyLike();
        $like->setProperty($property)
             ->setUser($user);

        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code'    => 200,
            'message' => 'Like bien ajouté',
            'likes'   => $likeRepo->count(['property' => $property])
        ], 200);
    }
}
