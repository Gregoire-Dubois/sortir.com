<?php

namespace App\Controller;



use App\Entity\Participant;
use App\Form\ParticipantType;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\String\Slugger\SluggerInterface;

class ParticipantController extends AbstractController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher=$passwordHasher;
    }
    /**
     * @Route("/profil", name="participant_afficherSonProfil")
     */
    public function afficherSonProfil(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger): Response
    {
        $participant=$this->getUser();

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

                try
                {
                    $file->move($this->getParameter('upload_photo'), $newFilename);
                }
                catch (FileException $e)
                {
                    //TODO: Message d'erreur?
                }
            }
            $participant->setPhoto($newFilename);

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a bien été modifié!');
            return $this->redirectToRoute("participant_afficherSonProfil", [
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
     * @Route("/profil/{id}", name="participant_afficherProfil", requirements={"id"="\d+"})
     */
    public function afficherProfil(Participant $participant): Response
    {
        if($participant === $this->getUser()){
            return $this->redirectToRoute('participant_profil');
        }else{
            return $this->render('participant/afficher_profil.html.twig', [
                'participant'=>$participant,
            ]);
        }

    }
}
