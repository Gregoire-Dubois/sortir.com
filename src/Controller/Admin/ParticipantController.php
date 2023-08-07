<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Form\ImportType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route("/admin", name="admin_")
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route ("/participants", name="listeParticipants")
     */
    public function listeUtilisateur(
        ParticipantRepository $participantRepository,
        SortieRepository      $sortieRepository
    ): Response
    {
        //Pour ne pas récupérer l'utilisateur connecté et l'utilisateur anonyme
        $participants = $participantRepository->selectParticipants($this->getUser());

        foreach ($participants as $participant) {
            // Récupérer les sorties associées à chaque participant
            $sorties = $sortieRepository->findSortiesByParticipant($participant);
            $participant->sorties = $sorties;
        }

        return $this->render('admin/participant/gestionParticipants.html.twig', [
            'participants' => $participants,
        ]);
    }

    /**
     * @Route("/import-csv", name="app_import_csv", methods={"GET", "POST"})
     */
    public function importCsv(
        EntityManagerInterface $entityManager,
        CampusRepository       $campusRepository,
        Request                $request
    ): Response
    {
        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        // On déclare un tableau qui stockera les nouveaux participants pour les afficher
        $aNewParticipants = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $sFile = $form->get('file')->getData();

            if ($sFile) {
                try {
                    // On teste que cette ressource est bien présente (!== false)
                    if (($handle = fopen($sFile, "r")) !== FALSE) {
                        while (($aLine = fgetcsv($handle, 1024, ";")) !== FALSE) {
                            $participant = new Participant();
                            // On assigne telle colonne comme valeur de telle propriété (vérifier quelle valeur correspond à quelle colonne) :
                            $participant->setEmail($aLine[1]);
                            // Dans le fichier les rôles ont bien des crochets, mais la lecture du fichier les retourne comme chaînes la fonction json_decode() nous aide à obtenir un tableau
                            $aRoles = json_decode($aLine[2], TRUE);

                            $participant->setRoles($aRoles);
                            $participant->setPassword($aLine[3]);
                            $participant->setPseudo($aLine[4]);
                            $participant->setNom($aLine[5]);
                            $participant->setPrenom($aLine[6]);
                            $participant->setTelephone($aLine[7]);
                            $participant->setActif($aLine[8]);
                            $participant->setPhoto($aLine[9]);
                            $campus = $campusRepository->find($aLine[11]);
                            if ($campus != null) {
                                $participant->setCampus($campus);
                            } else {
                                $participantConnecte = $this->getUser();
                                $participant->setCampus($participantConnecte->getCampus());
                            }
                            $dateFormat = 'd/m/Y H:i';
                            $date = DateTime::createFromFormat($dateFormat, $aLine[10]);
                            $participant->setDateCreation($date);

                            $entityManager->persist($participant);
                            // Comme on souhaite afficher les nouveaux participants dans la vue, on est obligé de faire le flush ici, ainsi, on peut récupérer l'id créé
                            $entityManager->flush();
                            // On met le nouveau participant dans le tableau à transmettre à la vue
                            $aNewParticipants[] = $participant;
                        }
                        // On ferme le fichier (= supprime la ressource en mémoire)
                        fclose($handle);
                    }
                } catch (FileException $e) {
                    //TODO: Message d'erreur?
                }
            }
        }
        // On appelle la vue en lui passant le tableau des nouveaux participants
        return $this->render('admin/participant/charger_fichier_csv.html.twig', [
            'aNewParticipants' => $aNewParticipants,
            'form' => $form->createView()]);
    }

    /**
     * @Route("/desactiver", name="desactiver")
     */
    public function desactiverParticipants(
        ParticipantRepository $participantRepository,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager
    ) {
        if ($request->isMethod('POST')) {
            $token = new CsrfToken('desactivation_participant', $request->request->get('_csrf_token'));
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $participantsSelectionnes = $request->request->get('participants', []);
            $listeParticipantsDesactives = [];

            foreach ($participantsSelectionnes as $participantId) {
                $participant = $participantRepository->find($participantId);
                if ($participant) {
                    $participant->setActif(false);
                    $listeParticipantsDesactives[] = $participant->getPseudo();
                    $entityManager->persist($participant);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les participants suivants ont bien été désactivés : ' . implode(', ', $listeParticipantsDesactives) . '.');
            return $this->redirectToRoute('admin_listeParticipants');
        }

        return new Response();
    }

    /**
     * @Route("/reactiver", name="reactiver")
     */
    public function reactiverParticants(
        ParticipantRepository $participantRepository,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager
    ) {
        if ($request->isMethod('POST')) {
            $token = new CsrfToken('reactivation_participant', $request->request->get('_csrf_token'));
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }
            $participantsSelectionnes = $request->request->get('participants', []);

            $listeParticipantsReactives = [];

            foreach ($participantsSelectionnes as $participantId) {
                $participant = $participantRepository->find($participantId);
                $participant->setActif(true);
                $listeParticipantsReactives[] = $participant->getPseudo();
                $entityManager->persist($participant);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les participants suivants ont bien été réactivés : ' . implode(', ', $listeParticipantsReactives) . '.');
            return $this->redirectToRoute('admin_listeParticipants');
        }

        return new Response();
    }


    /**
     * @Route("/supprimer", name="supprimer")
     */
    public function supprimerParticipants(
        ParticipantRepository     $participantRepository,
        Request                   $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface    $entityManager,
        SortieRepository          $sortieRepository)
    {
        if ($request->isMethod('POST')) {
            $token = new CsrfToken('suppression_participant', $request->request->get('_csrf_token'));
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $participantsSelectionnes = $request->request->get('participants', []);

            $listeParticipantsSupprimes = [];

            foreach ($participantsSelectionnes as $participantId) {
                $participant = $participantRepository->find($participantId);

                $listeParticipantsSupprimes[] = $participant->getPseudo();

                $sortiesOuvertes = $sortieRepository->selectSortiesOuverte($participant);

                foreach ($sortiesOuvertes as $sortie) {
                    if ($sortie->getParticipants()->contains($participant)) {
                        $sortie->removeParticipant($participant);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                    }
                }
                //On récupère les sorties ouvertes et créées
                $sortiesOuvertesOuCreees = $sortieRepository->selectSortiesOuvertesEtCreeesCloturee($participant);
                //On les supprime
                foreach ($sortiesOuvertesOuCreees as $sortie) {
                    $entityManager->remove($sortie);
                    $entityManager->flush();
                }

                $participantAnonyme = $participantRepository->findOneByEmail('testanonyme@test.com');

                //Remplacer ses occurrences par "utilisateur supprime" pour les sorties passées
                //On récupère les sorties passées
                $sortiesPassees = $sortieRepository->selectSortiesPassees($participant);

                foreach ($sortiesPassees as $sortie) {
                    if ($sortie->getOrganisateur() === $participant) {
                        $sortie->setOrganisateur($participantAnonyme);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                    } elseif ($sortie->getParticipants()->contains($participant)) {
                        $sortie->removeParticipant($participant);
                        $sortie->addParticipant($participantAnonyme);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                    }
                    //supprimer le participant
                    $entityManager->remove($participant);
                    $entityManager->flush();
                }

                $entityManager->flush();

                $this->addFlash('success', 'Le participant suivant a bien été supprimé :' . implode(', ', $listeParticipantsSupprimes) . ' .');
                return $this->redirectToRoute('admin_listeParticipants');
            }
        }
        return new Response();
    }
}
