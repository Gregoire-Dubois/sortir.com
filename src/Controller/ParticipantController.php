<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ImportType;
use App\Form\ParticipantType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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
                    $participant->setPhoto($newFilename);
                }
                catch (FileException $e)
                {
                    //TODO: Message d'erreur?
                }
            }


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
        /*foreach ($participantForm->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }*/
        return $this->render('participant/modifier_profil.html.twig', [
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
            return $this->redirectToRoute('participant_afficherSonProfil');
        }else{
            return $this->render('participant/afficher_profil.html.twig', [
                'participant'=>$participant,
            ]);

        }

    }

    /**
     * @Route("/import-csv", name="app_import_csv", methods={"GET", "POST"})
     */
    public function importCsv(
        EntityManagerInterface $entityManager,
        CampusRepository $campusRepository,
        Request $request,
        SluggerInterface $slugger
    ): Response
    {

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        // On déclare un tableau qui stockera les nouveaux participants pour les afficher
        $aNewParticipants = [];

        if ($form->isSubmitted() && $form->isValid()) {

            $sFile = $form->get('file')->getData();

            if($sFile){
                //$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                //$safeFilename = $slugger->slug($originalFilename);
                //$newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try
                {
                    //$file->move($this->getParameter('upload_photo'), $newFilename);

            // Pour la phase de développement, on peut vider la table...
            // ... à chaque fois avec l'instruction SQL 'TRUNCATE'
            // cf. https://code2dev.go.yo.fr/cours/symfony/doctrine_faq.php#h2_6
            //$connection = $entityManager->getConnection();
            //$platform = $connection->getDatabasePlatform();
            //$connection->executeQuery($platform->getTruncateTableSQL('participant'));

            // Chemin vers le fichier
            // $this->getParameter('kernel.project_dir') récupère le chemin racine du projet (genre 'c:/wamp/www/projet')
            //$sFile = $this->getParameter('kernel.project_dir') . '/private/csv/participants.csv';



            // On ouvre le fichier en mode lecture ('r')
            // $handle représente le fichier = une ressource (une sorte d'objet avec les métadonnées du fichier)
            // On teste que cette ressource est bien présente (!== false)
            if (($handle = fopen($sFile, "r")) !== FALSE) {
                // On parcourt le fichier ligne par ligne
                // fgetcsv() découpe chaque ligne par rapport au séparateur (';')
                // et met retourne les données dans un tableau, ici $aLine
                // les indices du tableau coorespondent à la position des valeurs/colonnes dans le fichier CSV
                while (($aLine = fgetcsv($handle, 1024, ";")) !== FALSE) {
                    // Pour chaque nouvelle ligne, on crée un objet Participant
                    $participant = new Participant();

                    // On assigne telle colonne comme valeur de telle propriété (vérifier quelle valeur correspond à quelle colonne) :
                    $participant->setEmail($aLine[1]);

                    // Dans le fichier les rôles ont bien des crochets
                    // mais la lecture du fichier les retourne comme chaînes
                    // la fonction json_decode() nous aide à obtenir un tableau
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
                    $participant->setCampus($campus);
                    $dateFormat = 'd/m/Y H:i';
                    $date = \DateTime::createFromFormat($dateFormat,$aLine[10] );
                   // dump($date);
                    $participant->setDateCreation($date);
                    // On persiste l'objet courant
                    $entityManager->persist($participant);

                    // Comme on souhaite afficher les nouveaux participants dans la vue,
                    // on est obligé de faire le flush ici, ainsi on peut récupérer l'id créé
                    $entityManager->flush();

                    // On met le nouveau participant dans le tableau à transmettre à la vue
                    $aNewParticipants[] = $participant;
                }

                // On ferme le fichier (= supprime la ressource en mémoire)  s
                fclose($handle);
            }
                }
                catch (FileException $e)
                {
                    //TODO: Message d'erreur?
                }
            }
        }
            // On appelle la vue en lui passant le tableau des nouveaux participants
            return $this->render('admin/participant/charger_fichier_csv.html.twig', [
                'aNewParticipants' => $aNewParticipants,
                'form' => $form->createView() ]);


    }

    /**
     * @Route("/admin/desactiver", name="admin_desactiver")
     */
    public function desactiverParticants(
        ParticipantRepository $participantRepository,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager){

        $participantsActifs = $participantRepository->selectParticipantsActifs($this->getUser());

        if($request->isMethod('POST')){
            $token = new CsrfToken('desactivation_participant', $request->request->get('_csrf_token'));
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }
            //dump($request);
            $participantsSelectionnes= $request->request->get('participants', []);
            //dump($participantsSelectionnes);
            $listeParticipantsDesactives = [];
            foreach ($participantsSelectionnes as $participantId){
                $participant = $participantRepository->find($participantId);
                $participant->setActif(false);
                $listeParticipantsDesactives[]=$participant->getPseudo();
                $entityManager->persist($participant);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Les participants suivants ont bien été désactivés :' . implode(', ', $listeParticipantsDesactives) . ' .');
            return $this->redirectToRoute('admin_desactiver');
        }


        return $this->render('admin/participant/desactiver.html.twig',[
            'participantsActifs'=>$participantsActifs
        ]);
    }

    /**
     * @Route("/admin/reactiver", name="admin_reactiver")
     */
    public function reactiverParticants(
        ParticipantRepository $participantRepository,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager){

        $participantsInactifs = $participantRepository->selectParticipantsInactifs();

        if($request->isMethod('POST')){
            $token = new CsrfToken('reactivation_participant', $request->request->get('_csrf_token'));
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }
            //dump($request);
            $participantsSelectionnes= $request->request->get('participants', []);
            //dump($participantsSelectionnes);
            $listeParticipantsReactives = [];
            foreach ($participantsSelectionnes as $participantId){
                $participant = $participantRepository->find($participantId);
                $participant->setActif(true);
                $listeParticipantsReactives[]=$participant->getPseudo();
                $entityManager->persist($participant);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Les participants suivants ont bien été réactivés :' . implode(', ', $listeParticipantsReactives) . ' .');
            return $this->redirectToRoute('admin_reactiver');
        }


        return $this->render('admin/participant/reactiver.html.twig',[
            'participantsInactifs'=>$participantsInactifs
        ]);
    }

    /**
     * @Route("/admin/supprimer", name="admin_supprimer")
     */
    public function supprimerParticipants(
        ParticipantRepository $participantRepository,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository){

        $participants = $participantRepository->selectParticipants($this->getUser());

      //  $sortie25 = $sortieRepository->find('25');
       // dump($sortie25);
        //dump($sortie25->getParticipants());
        if($request->isMethod('POST')){
            $token = new CsrfToken('suppression_participant', $request->request->get('_csrf_token'));
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }
          //  dump($request);
            $participantsSelectionnes= $request->request->get('participants', []);

            //dump($participantsSelectionnes);
            $listeParticipantsSupprimes = [];

            foreach ($participantsSelectionnes as $participantId){
                $participant = $participantRepository->find($participantId);
              //  dump($participant);
                $listeParticipantsSupprimes[]=$participant->getPseudo();

                //Désinscrire des sorties ouvertes
                    //On récupère les sorties ouvertes
                $sortiesOuvertes = $sortieRepository->selectSortiesOuverte($participant);
                //dump($sortiesOuvertes);

                    //On le désinscrit de ces sorties
                foreach($sortiesOuvertes as $sortie) {
                  //  dump($sortie->getParticipants());
                   // dump($sortie->getParticipants()->contains($participant));
                    if ($sortie->getParticipants()->contains($participant)) {

                        $sortie->removeParticipant($participant);

                        $entityManager->persist($sortie);
                        //dump($participant);
                        $entityManager->flush();
                        //dump($sortie);
                    }
                }
                //Supprimer les sorties dont il est l'organisateur

                    //On récupère les sorties ouvertes et créées
                $sortiesOuvertesOuCreees = $sortieRepository->selectSortiesOuvertesEtCreeesCloturee($participant);
                    //On les supprime
                foreach ($sortiesOuvertesOuCreees as $sortie){
                    //dump($sortie);
                    $entityManager->remove($sortie);
                    $entityManager->flush();
                }

                $participantAnonyme = $participantRepository->findOneByEmail('testanonyme@test.com');
               // dump($participantAnonyme);
                //Remplacer ses occurences par "utilisateur supprime" pour les sorties passees
                    //On recupere les sorties passees
                $sortiesPassees = $sortieRepository->selectSortiesPassees($participant);
                //dump($sortiesPassees);
                foreach($sortiesPassees as $sortie) {
                   // if($sortie instanceof Sortie) {
                  //      dump($sortie->getParticipants());
                   // }
                   // dump($sortie->getOrganisateur()===$participant);
                    if($sortie->getOrganisateur()===$participant){
                        $sortie->setOrganisateur($participantAnonyme);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                    }elseif ($sortie->getParticipants()->contains($participant)){
                        /*$participants = $sortie->getParticipants();
                        $index = array_search($participant, $participants);
                        if ($index !== false) {
                            $participants[$index] = $participantAnonyme;
                            $sortie->
                            $entityManager->persist($sortie);
                            $entityManager->flush();
                        }*/
                        $sortie->removeParticipant($participant);
                        $sortie->addParticipant($participantAnonyme);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                    }
                   /* $participants = $sortie->getParticipants();
                    dump($participants);
                    foreach ($participants as $part){
                        if($part == $participant){
                            dump($part);
                            dump($participant);
                            $participants[$part] = "Participant supprimé";
                        }
                    }
                    $sortie->setParticipants($participants);
                    $entityManager->persist($sortie);
                    $entityManager->flush();*/
                }




                //supprimer le participant
                $entityManager->remove($participant);


                $entityManager->flush();
            }

            $this->addFlash('success', 'Les participants suivants ont bien été supprimés :' . implode(', ', $listeParticipantsSupprimes) . ' .');
            return $this->redirectToRoute('admin_supprimer');
        }


        return $this->render('admin/participant/supprimer.html.twig',[
            'participants'=>$participants
        ]);
    }

}
