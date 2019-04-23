<?php

namespace App\Controller\Admin;

use App\Entity\Picture;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/admin/picture")
 */
class AdminPictureController extends AbstractController
{
    /**
     * @Route("/{id}", name="admin.picture.delete", methods="DELETE")
     */
    public function delete(Picture $picture, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete' .$picture->getId(), $data['_token'])) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($picture);
            $entityManager->flush();
            // $this->addFlash('success', 'Image supprimée avec succès');
            return new JsonResponse(['success' => 1]);
        }
        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}
