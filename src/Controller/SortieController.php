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
}
