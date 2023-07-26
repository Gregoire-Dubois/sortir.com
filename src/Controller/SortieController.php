<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties", name="sortie_listeSortie")
     */
    public function listeSortie(): Response
    {
        return $this->render('sortie/liste.html.twig', [

        ]);
    }

    /**
     * @Route("/sorties/detail/{id}", name="sortie_detailSortie")
     */
    public function detailSortie(int $id): Response
    {
        return $this->render('sortie/detail.html.twig', [

        ]);
    }

    /**
     * @Route("/creation", name="sortie_creerSortie")
     */
    public function creerSortie(): Response
    {
        return $this->render('sortie/creation.html.twig', [

        ]);
    }

    /**
     * @Route("/sorties/publier/{id}", name="sortie_publierSortie")
     */
    public function publierSortie(int $id): Response
    {
        return $this->redirectToRoute('sortie_listeSortie');
    }

    /**
     * @Route("/sorties/annuler/{id}", name="sortie_annulerSortie")
     */
    public function annulerSortie(int $id): Response
    {
        return $this->redirectToRoute('sortie_listeSortie');
    }
}
