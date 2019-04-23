<?php

namespace App\Controller\Admin;

use App\Repository\PropertyRepository;
use App\Entity\Property;
use App\Form\PropertyType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager;
// use Liip\ImagineBundle\Imagine\Cache\CacheManager;
// use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class AdminPropertyController extends AbstractController
{

    private $repository;
    private $em;

    public function __construct(PropertyRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/admin", name="admin.property.index")
     */
    public function index()
    {
        $properties = $this->repository->findAll();
        // dump($properties);
        return $this->render('admin/property/index.html.twig', [
            'properties'   => $properties,
            'current_menu' => 'admin'
        ]);
    }

    /**
     * @Route("/admin/property/{id}-edit", name="admin.property.edit")
     */
    public function edit(Property $property, Request $request): Response
    {
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            // if ($property->getImageFile() instanceof UploadedFile){
            //     $cachemanager->remove($helper->asset($property, 'imageFile'));
            // }
            $this->em->flush();
            $this->addFlash('success', 'Bien modifié avec succès');
            return $this->redirectToRoute('admin.property.index');
        }
        return $this->render('admin/property/edit.html.twig', [
            'property' => $property,
            'form'     => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/property/new", name="admin.property.new", methods="GET|POST")
     */
    public function new(Request $request)
    {
        $property = new Property();
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $this->em->persist($property);
            $this->em->flush();
            $this->addFlash('success', 'Bien créé avec succès');
            return $this->redirectToRoute('admin.property.index');
        }
        return $this->render('admin/property/new.html.twig', [
            'property' => $property,
            'form'     => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/property/delete-{id}", name="admin.property.delete", methods="DELETE")
     */
    public function delete(Property $property, Request $request)
    {
        if ($this->isCsrfTokenValid('delete' . $property->getId(), $request->get('_token'))){
            $this->em->remove($property);
            $this->em->flush();
            $this->addFlash('success', 'Bien supprimé avec succès');
            return $this->redirectToRoute('admin.property.index');
        }
    }
}