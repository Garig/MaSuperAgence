<?php

namespace App\Controller\Admin;

use App\Entity\Option;
use App\Form\OptionType;
use App\Repository\OptionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/option")
 */
class AdminOptionController extends AbstractController
{
    /**
     * @Route("/", name="admin.option.index", methods={"GET"})
     */
    public function index(OptionRepository $optionRepository): Response
    {
        return $this->render('admin/option/index.html.twig', [
            'options' => $optionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin.option.new", methods={"GET","POST"})
     */
    public function new(Request $request, OptionRepository $repository): Response
    {
        $option = new Option();
        $form = $this->createForm(OptionType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // symfony2&3
            // $name = $form->get('name')->getData();
            // symfony4
            // $name = $form['name']->getData();
            // ou
            // $name = $request->request->get('name');
            // $name = $request->request->get('name');
            // Reccuperation du contenu de l'array ayant comme champ ideleve //
            // $name = $form['name'];
            // dum
            // dump($request->request);
            // die();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($option);
            $entityManager->flush();
            $this->addFlash('success', 'Option créé avec succès');
            return $this->redirectToRoute('admin.option.index');

        }



        return $this->render('admin/option/new.html.twig', [
            'option' => $option,
            'form' => $form->createView(),
            // 'data' => $data
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin.option.edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Option $option): Response
    {
        $form = $this->createForm(OptionType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Option modifiée avec succès');
            return $this->redirectToRoute('admin.option.index', [
                'id' => $option->getId(),
            ]);
        }

        return $this->render('admin/option/edit.html.twig', [
            'option' => $option,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin.option.delete", methods={"DELETE"})
     */
    public function delete(Request $request, Option $option): Response
    {
        if ($this->isCsrfTokenValid('delete'.$option->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($option);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Option supprimée avec succès');
        return $this->redirectToRoute('admin.option.index');
    }
}
