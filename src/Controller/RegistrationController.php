<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Security\AppAuthentifcatorAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/admin/inscription", name="app_register")
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas les droits d'accès !")
     */
    public function creerParticipant(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthentifcatorAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();

        //$user->setRoles(["ROLE_PARTICIPANT"]);

        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

       // dump($participant->getRoles());
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $role = $form->get('role')->getData();
            $participant->setActif(true);
            if($role==="NON"){
                $participant->setRoles(["ROLE_PARTICIPANT"]);
            }else{
                $participant->setRoles(["ROLE_ADMIN"]);
            }
            dump($role);
            $participant->setPassword(
            $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );
            $participant->setActif("true");

            dump($form->getData());

            $participant->setDateCreation(new \DateTime());

            $entityManager->persist($participant);
            $entityManager->flush();
            // do anything else you need here, like send an email

            $this->addFlash('success', 'Le participant a bien été inscrit!');

            return $this->redirectToRoute('app_register');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
