<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ParticipantController extends AbstractController
{
    /**
     * @Route("/profil/{id}", name="utilisateur_afficherProfil")
     */
    public function afficherProfil(int $id): Response
    {
        return $this->render('participant/afficher_profil.html.twig', [

        ]);
    }

    /**
     * @Route("/profil/modification", name="utilisateur_modifierProfil")
     */
    public function modifierProfil(int $id): Response
    {
        return $this->render('participant/modifier_profil.html.twig', [

        ]);
    }
}
