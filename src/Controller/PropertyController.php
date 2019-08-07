<?php

namespace App\Controller;

use App\Entity\Property;
use App\Entity\Contact;
use App\Entity\PropertySearch;
use App\Form\PropertySearchType;
use Twig\Environment;
use App\Notification\ContactNotification;
use App\Form\ContactType;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
//si on utilise l autowiring/
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
//si on avait utilisé le container mais c est pas comme ça qu on declare il faut aller dans services
// use Knp\Component\Pager\Paginator;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Translation\TranslatorInterface;



class PropertyController extends AbstractController
{
    /**
     * @var PropertyRepository
     */
    private $repository;

    public function __construct(PropertyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route("/{_locale}/biens", name="property.index")
     * requirements:
     *     _locale: fr|en
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        // $repository = $this->getDoctrine()->getRepository(Property::class);
        // dump($repository);
        
        // $property = $this->repository->findAllVisible();
        // dump($property);

        //systeme de filtre
        //créé une entité qui va représenter notre recherche
        //créé un formulaire
        //gérer l'affichage du formulaire
        //gérer le traitement dans notre controller

        $search = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $search);
        $form->handleRequest($request);

        // $paginator  = $this->get('knp_paginator');
        $properties = $paginator->paginate(
            $this->repository->findAllVisibleQuery($search), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            6/*limit per page*/
        );
        return $this->render('property/index.html.twig', [
            'current_menu' => 'properties',
            'properties'   => $properties,
            'form'         => $form->createView()
        ]);
    }

    /**
     * @Route("/{_locale}/biens/{slug}-{id}", name="property.show", requirements={"slug": "[a-z0-9\-]*", })
     * requirements:
     *     _locale: fr|en
     */
    public function show(Property $property, string $slug, TranslatorInterface $translator, Request $request, \Swift_Mailer $mailer, Environment $renderer, ContactNotification $notification) : Response
    {
        if ($property->getSlug() !== $slug) {
            return $this->redirectToRoute('property.show', [
                'id'   => $property->getId(),
                'slug' => $property->getSlug()
            ], 301);
        }
        $contact = new Contact();
        $contact->setProperTy($property);
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $notification->notify($contact);
            $message = (new \Swift_Message('Agence: ' .$contact->getProperty()->getTitle()))
            ->setFrom(['alexandre.houriez5@gmail.com'=> 'Mon Agence'])            
            ->setTo('alexandre.houriez@yahoo.fr')
            ->setReplyTo($contact->getEmail())
            ->setBody($renderer->render('emails/contact.html.twig', [
                'contact' => $contact
            ]), 'text/html');
            $mailer->send($message);

            $this->addFlash('success', $translator->trans('email.message.success'));
            return $this->redirectToRoute('property.show', [
                'id'   => $property->getId(),
                'slug' => $property->getSlug()
            ]);
            // ici ce sera pas une 301 on fait une redirection suite a un traitement corrct du formulaire
        }


        if(isset($_POST['g-recaptcha-response'])){
            $recaptcha = new ReCaptcha("6LdkbZ0UAAAAAHerBAiNFmD0MqdNLon2mq-1PBPd");
            $resp = $recaptcha->verify($_POST['g-recaptcha-response']);
            if ($resp->isSuccess()) {
                // Verified!
                // var_dump('Captcha valide');
            } else {
                $errors = $resp->getErrorCodes();
                // var_dump('Captcha invalide');
                // var_dump($errors);
            }
        }

        //$property = $this->repository->find($property);
        return $this->render('property/show.html.twig', [
            'property'     => $property,
            'current_menu' => 'properties',
            'form'         => $form->createView()
        ]);
    }
}