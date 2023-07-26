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
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function afficherProfil(
        int $id,
        ParticipantRepository $participantRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger): Response
    {
        $participant=$participantRepository->find($id);

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){

            $participant = $participantForm->getData();
            $plainPassword = $participantForm->get('plainPassword')->getData();

            if($plainPassword !== null){
                $participant->setPassword($this->passwordHasher->hashPassword($participant, $plainPassword));
            }

            $file = $participantForm->get('photo')->getData();

            if($file){
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                $file->move($this->getParameter('upload_photo'), $newFilename);
            }
            $participant->setPhoto($newFilename);

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
            'participantForm' => $participantForm->createView(),
            'participant'=>$participant,
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
