<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Form\ParticipantType;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
class ParticipantController extends AbstractController
{
    /**
     * @Route("/profil/{id}", name="utilisateur_afficherProfil")
     * @Method("GET")
     */
    public function afficherProfil(int $id, ParticipantRepository $participantRepository, Request $request): Response
    {
        $participant=$participantRepository->find($id);
        $participant->setPassword('');


        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        return $this->render('participant/afficher_profil.html.twig', [
            'participantForm' => $participantForm->createView()
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
