<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Form\ParticipantType;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
class ParticipantController extends AbstractController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher=$passwordHasher;
    }
    /**
     * @Route("/profil/{id}", name="utilisateur_afficherProfil")
     * @Method("GET")
     */
    public function afficherProfil(int $id, ParticipantRepository $participantRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant=$participantRepository->find($id);

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){
            $participant->setPassword($this->passwordHasher->hashPassword($participant,$participant->getPassword()));

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a bien été modifié!');
            return $this->redirectToRoute("utilisateur_afficherProfil", [
                'id'=>$participant->getId()
            ]);
        }else
        {
            $entityManager->refresh($participant);
        }

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
