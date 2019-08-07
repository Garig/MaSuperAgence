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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
// use Symfony\Component\EventDispatcher\EventSubscriberInterface;
// use Symfony\Component\HttpKernel\Event\RequestEvent;
// use Symfony\Component\HttpKernel\KernelEvents;

class HomeController extends AbstractController
{

    //la tu remarquera on a pas declaré le $repository dans __construct et ça marche quand meme sinon on aurait mis
    //$properties = $this->repository->findBy() et on aurait pas eu a typehinté la méthode index()
    /**
     * @Route("/{_locale}/", name="home")
     * requirements:
     *       _locale: fr|en
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
     * @Route("/", name="redirection")
     */
    public function redirection(): Response
    {
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/fr/", name="languageFr")
     */
    public function languageFr(): Response
    {
        $request->setLocale('fr');
    }

    /**
     * @Route("/en/", name="languageEn")
     */
    public function languageEn(Request $request): Response
    {
        // $request->setLocale('en');
        // echo $request->getLocale();
        // die();

        // echo $request->getLocale();
        // $request->setLocale('fr');
        // echo $request->getLocale();
        // $attributs = $request->attributes;
        // // var_dump($attributs);
        // var_dump($attributs->getLocale());
        // die();

        // $request->setLocale('en');
            // $this->get('request')->attributes->set('_locale', 'en');
            // $request->getSession()->set('_locale', 'en');
        // $request->attributes->get('_route');
        $request->setLocale('en');

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

    /**
     * @Route("/{_locale}", name="language")
     * requirements:
     *     _locale: fr|en
    */
    // public function language(SessionInterface $session, Request $request, $langue = null)
	// {
	    
    //     // $locale = $request->getLocale();

    //     // var_dump($locale);die();        
    //     if($langue != null)
	//     {
    //         // On enregistre la langue en session
	//         $session->set('_locale', $langue);
	//     }
 
	//     // on tente de rediriger vers la page d'origine
    //     return $this->redirectToRoute('home');
    // }
}
