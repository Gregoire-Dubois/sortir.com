<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Event\SortieEvent;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class AppFixtures extends Fixture
{
    protected static string $defaultName = 'app:fixtures:load';
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher, SortieEvent $sortieEtats)
    {
        $this->passwordHasher=$passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création de 3 campus
        $nomsCampus = ['SAINT HERBLAIN', 'CHARTRES DE BRETAGNE', 'LA ROCHE SUR YON'];

        $campusArray = [];

        foreach ($nomsCampus as $nomCampus){
            $campus = new Campus();
            $campus->setNom($nomCampus);
            $manager->persist($campus);
            $campusArray[] = $campus;
        }

        // Flush pour enregistrer les campus dans la base de données
        $manager->flush();

        // Affichage du nombre de campus enregistrés
        $nombreCampusEnregistres = count($campusArray);
        dump('Nombre de campus enregistrés : ' . $nombreCampusEnregistres);

        //Création des 20 plus grandes villes de France
        $nomsVilles =[
            ['nom' => 'PARIS', 'codePostal' => '75000'],
            ['nom' => 'MARSEILLE', 'codePostal' => '13000'],
            ['nom' => 'LYON', 'codePostal' => '69000'],
            ['nom' => 'TOULOUSE', 'codePostal' => '31000'],
            ['nom' => 'NICE', 'codePostal' => '06000'],
            ['nom' => 'NANTES', 'codePostal' => '44000'],
            ['nom' => 'MONTPELLIER', 'codePostal' => '34000'],
            ['nom' => 'STRASBOURG', 'codePostal' => '67000'],
            ['nom' => 'BORDEAUX', 'codePostal' => '33000'],
            ['nom' => 'LILLE', 'codePostal' => '59000'],
            ['nom' => 'RENNES', 'codePostal' => '35000'],
            ['nom' => 'REIMS', 'codePostal' => '51000'],
            ['nom' => 'SAINT ETIENNE', 'codePostal' => '42000'],
            ['nom' => 'LE HAVRE', 'codePostal' => '76000'],
            ['nom' => 'TOULON', 'codePostal' => '83000'],
            ['nom' => 'GRENOBLE', 'codePostal' => '38000'],
            ['nom' => 'DIJON', 'codePostal' => '21000'],
            ['nom' => 'ANGERS', 'codePostal' => '49000'],
            ['nom' => 'NÎMES', 'codePostal' => '30000'],
            ['nom' => 'VILLEURBANNE', 'codePostal' => '69100'],
        ];

        $villeArray = [];

        foreach ($nomsVilles as $nomVille) {
            $ville = new Ville();
            $ville->setNom($nomVille['nom']);
            $ville->setCodePostal($nomVille['codePostal']);
            $manager->persist($ville);
            $villeArray[] = $ville;
        }

        // Flush pour enregistrer les villes dans la base de données
        $manager->flush();

        // Affichage du nombre de villes enregistrées
        $nombreVillesEnregistrees = count($villeArray);
        dump('Nombre de villes enregistrées : ' . $nombreVillesEnregistrees);

        //Création de 100 lieux aléatoires (5 par villes)
        $faker = Faker\Factory::create('fr_FR');

        // Récupération de la liste des villes
        $villes = $manager->getRepository(Ville::class)->findAll();

		// Initialisation du tableau de compteur de lieux par ville
        $compteursVilles = array_fill_keys(array_map(fn($ville) => $ville->getId(), $villes), 0);

        $lieuArray = [];

        for ($i = 1; $i <= 100; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);

            // Trouver la ville avec le compteur le plus bas
            $villeAvecCompteurMin = array_keys($compteursVilles, min($compteursVilles))[0];
            // Mettre à jour le compteur pour la ville choisie
            $compteursVilles[$villeAvecCompteurMin]++;
            // Affecter la ville au lieu
            $lieu->setVille($villes[array_search($villeAvecCompteurMin, array_map(fn($ville) => $ville->getId(), $villes))]);

            $manager->persist($lieu);
            $lieuArray[] = $lieu;
        }

        // Affichage du nombre de lieux enregistrés
        $nombreLieuxEnregistres = count($lieuArray);
        dump('Nombre de lieux enregistrés : ' . $nombreLieuxEnregistres);

        // Flush pour enregistrer les lieux dans la base de données
        $manager->flush();

        // Création de 90 participants aléatoires
        $faker = Faker\Factory::create('fr_FR');

        // Récupération de la liste des campus
        $campus = $manager->getRepository(Campus::class)->findAll();

        // Initialisation du tableau de compteur de participants par campus
        $compteursCampus = array_fill_keys(array_map(fn($campus) => $campus->getId(), $campus), 0);

        $participantsArray = [];

        for($i=0;$i<90;$i++)
        {
            $participant = new Participant();
            $participant->setNom($faker->lastName());
            $participant->setPrenom($faker->firstName());
            $participant->setPassword($this->passwordHasher->hashPassword($participant,'azerty'));
            $participant->setRoles(['ROLE_PARTICIPANT']);
            $participant->setActif(true);
            $participant->setDateCreation(new DateTimeImmutable());
            $participant->setPhoto('Photo_Profil_Defaut_Test.png');

            // Trouver le campus avec le compteur le plus bas
            $campusAvecCompteurMin = array_keys($compteursCampus, min($compteursCampus))[0];
            // Mettre à jour le compteur pour le campus choisis
            $compteursCampus[$campusAvecCompteurMin]++;
            // Affecter le participant au campus
            $participant->setCampus($campus[array_search($campusAvecCompteurMin, array_map(fn($campus) => $campus->getId(), $campus))]);

            $manager->persist($participant);

            // Générer un email unique sous la forme prenom.nom+année(XXXX)@campus-eni.fr
            // Si l'email est déjà existant, on rajoute un nombre avant le prénom
            $anneeAleatoire = $faker->numberBetween(2018, 2023);
            $prenomSansAccents = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $participant->getPrenom());
            $nomSansAccents = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $participant->getNom());
            $emailBase = $prenomSansAccents . '.' . $nomSansAccents . $anneeAleatoire . '@campus-eni.fr';
            $email = $emailBase;
            $counter = 1;

            while (true) {
                $existingParticipant = $manager->getRepository(Participant::class)->findOneBy(['email' => $email]);
                if ($existingParticipant) {
                    // Mail déjà utilisé, on ajoute un chiffre incrémenté
                    $anneeAleatoireRecalculee = $anneeAleatoire - $counter;
                    $email = $prenomSansAccents . '.' . $nomSansAccents . $anneeAleatoireRecalculee . '@campus-eni.fr';
                    $counter++;
                } else {
                    // Mail unique, on l'attribue au participant
                    $participant->setEmail($email);
                    $manager->persist($participant);
                    break;
                }
            }

            // Générer un pseudo unique avec la première lettre du prénom et le nom de famille en entier
            // (Si déjà existant : Deux premières lettres du prénom + le nom de famille en entier, si déjà existant etc...)
            $basePseudo = substr($participant->getPrenom(), 0, 1) . $participant->getNom();
            $pseudo = $basePseudo;
            $originalPseudo = $basePseudo;
            $prenomLongueur = mb_strlen($participant->getPrenom(), 'UTF-8');
            $counter = 1;

            while (true) {
                $existingParticipant = $manager->getRepository(Participant::class)->findOneBy(['pseudo' => $pseudo]);
                if ($existingParticipant) {
                    // Pseudo déjà utilisé
                    if ($counter <= $prenomLongueur) {
                        // Ajouter une lettre supplémentaire du prénom
                        $pseudo = substr($participant->getPrenom(), 0, ++$counter) . $participant->getNom();
                    } else {
                        // Si le pseudo Prenom complet + Nom existe déjà, ajouter un chiffre incrémenté
                        $pseudo = $originalPseudo . $counter;
                        $counter++;
                    }
                } else {
                    // Pseudo unique, on l'attribue au participant
                    $participant->setPseudo($pseudo);
                    $manager->persist($participant);
                    break;
                }
            }

            // Générer un numéro de téléphone aléatoire sous la forme 06 00 00 00 00
            $numeroMobile = $faker->randomNumber(8, true);
            $formateNumeroTelephone = '06 ' . substr($numeroMobile, 0, 2) . ' ' . substr($numeroMobile, 2, 2) . ' ' . substr($numeroMobile, 4, 2) . ' ' . substr($numeroMobile, 6, 2);
            $participant->setTelephone($formateNumeroTelephone);

            $manager->persist($participant);
            $manager->flush();
            $participantsArray[] = $participant;
        }

        // Affichage du nombre de participants enregistrés
        $nombreParticipantsEnregistres = count($participantsArray);
        dump('Nombre de participants enregistrés : ' . $nombreParticipantsEnregistres);

        // Création des états de sortie
        $nomsEtats = [
            'Créée',
            'Ouverte',
            'Clôturée',
            'Activité en cours',
            'Passée',
            'Annulée',
            'Archivée',
        ];

        $etatsArray = [];

        foreach ($nomsEtats as $nomEtat) {
            $etat = new Etat();
            $etat->setLibelle($nomEtat);
            $manager->persist($etat);
            $etatsArray[] = $etat;
        }

        // Affichage du nombre d'états enregistrés
        $nombreEtatsEnregistres = count($etatsArray);
        dump("Nombre d'états enregistrés : " . $nombreEtatsEnregistres);

        $manager->flush();

        // Création des sorties aléatoires

        $nomsSorties = ['Bowling', 'Bar', 'Poker', 'Concert', 'Cinéma',
            'Randonnée', 'Restaurant', 'Soirée', 'Escape Game',
            'Chasse au Trésor', 'Blind Test', "Visite d'une Brasserie",
            "Parc d'Attraction", 'Billard', 'Virtual Reality', 'Camping',
            'Festival', 'Exposition', 'Theatre', 'Zoo', 'Match de Foot',
            'Match de Basket', 'Sortie à la Plage', 'Piscine'];

        $lieux = $manager->getRepository(Lieu::class)->findAll();
        $organisateur = $manager->getRepository(Participant::class)->findAll();

        $sortiesArray = [];

        // Création de 5 sorties avec un état "Créée"
        for ($i = 0; $i < 5; $i++) {
            // Générer une date aléatoire entre +1 semaine et +1 mois
            $now = new DateTime();
            $startDate = (clone $now)->modify('+1 week');
            $endDate = (clone $now)->modify('+1 month');
            $randomDate = $faker->dateTimeBetween($startDate, $endDate);

            $sortieCreee = new Sortie();
            $sortieCreee->setNom($faker->randomElement($nomsSorties));
            $sortieCreee->setDescription($faker->paragraph(1));
            $sortieCreee->setDateDebut($randomDate);
            $sortieCreee->setDuree($faker->numberBetween(60, 240));
            $dateLimiteInscription = (clone $randomDate)->modify('-1 day');
            $sortieCreee->setDateLimiteInscription($dateLimiteInscription);
            $sortieCreee->setNbInscritptionMax($faker->numberBetween(1, 20));
            $sortieCreee->setOrganisateur($faker->randomElement($organisateur));
            $sortieCreee->setLieu($faker->randomElement($lieux));
            $sortieCreee->setDateCreation(new DateTimeImmutable());
            $sortieCreee->setEtat($manager->getRepository(Etat::class)->findbyLibelle('Créée'));

            // Récupérer le campus de l'organisateur
            $campusOrganisateur = $sortieCreee->getOrganisateur()->getCampus();
            // Attribuer le campus de l'organisateur à la sortie
            $sortieCreee->setCampus($campusOrganisateur);

            $manager->persist($sortieCreee);
            $sortiesArray[] = $sortieCreee;
        }

        // Création de 15 sorties avec un état "Ouverte"
        for ($i = 0; $i < 15; $i++) {
            // Générer une date aléatoire entre +1 semaine et +1 mois
            $now = new DateTime();
            $startDate = (clone $now)->modify('+1 week');
            $endDate = (clone $now)->modify('+1 month');
            $randomDate = $faker->dateTimeBetween($startDate, $endDate);

            $sortieOuverte = new Sortie();
            $sortieOuverte->setNom($faker->randomElement($nomsSorties));
            $sortieOuverte->setDescription($faker->paragraph(1));
            $sortieOuverte->setDateDebut($randomDate);
            $sortieOuverte->setDuree($faker->numberBetween(60, 240));
            $dateLimiteInscription = (clone $randomDate)->modify('-1 day');
            $sortieOuverte->setDateLimiteInscription($dateLimiteInscription);
            $sortieOuverte->setNbInscritptionMax($faker->numberBetween(1, 20));
            $sortieOuverte->setOrganisateur($faker->randomElement($organisateur));
            $sortieOuverte->setLieu($faker->randomElement($lieux));
            $sortieOuverte->setDateCreation(new DateTimeImmutable());
            $sortieOuverte->setEtat($manager->getRepository(Etat::class)->findbyLibelle('Ouverte'));

            // Récupérer le campus de l'organisateur
            $campusOrganisateur = $sortieOuverte->getOrganisateur()->getCampus();
			// Attribuer le campus de l'organisateur à la sortie
            $sortieOuverte->setCampus($campusOrganisateur);

            $manager->persist($sortieOuverte);
            $sortiesArray[] = $sortieOuverte;
        }

        // Création de 5 sorties avec un état "Clôturée"
        for ($i = 0; $i < 5; $i++) {
            // Générer une date aléatoire entre -6 heures et -12 heures
            $now = new DateTime();
            $startDate = (clone $now)->modify('+6 hours');
            $endDate = (clone $now)->modify('+23 hours');
            $randomDate = $faker->dateTimeBetween($startDate, $endDate);

            $sortieCloturee = new Sortie();
            $sortieCloturee->setNom($faker->randomElement($nomsSorties));
            $sortieCloturee->setDescription($faker->paragraph(1));
            $sortieCloturee->setDateDebut($randomDate);
            $sortieCloturee->setDuree($faker->numberBetween(60, 240));
            $dateLimiteInscription = (clone $randomDate)->modify('-1 day');
            $sortieCloturee->setDateLimiteInscription($dateLimiteInscription);
            $sortieCloturee->setNbInscritptionMax($faker->numberBetween(1, 20));
            $sortieCloturee->setOrganisateur($faker->randomElement($organisateur));
            $sortieCloturee->setLieu($faker->randomElement($lieux));
            $sortieCloturee->setDateCreation(new DateTimeImmutable());
            $sortieCloturee->setEtat($manager->getRepository(Etat::class)->findbyLibelle('Clôturée'));

            // Récupérer le campus de l'organisateur
            $campusOrganisateur = $sortieCloturee->getOrganisateur()->getCampus();
            // Attribuer le campus de l'organisateur à la sortie
            $sortieCloturee->setCampus($campusOrganisateur);

            $manager->persist($sortieCloturee);
            $sortiesArray[] = $sortieCloturee;
        }

        // Création de 5 sorties avec un état "Activité en cours"
        for ($i = 0; $i < 5; $i++) {
            // Générer la date du jour
            $now = new DateTime();
            $randomDate = clone $now;

            $sortieEnCours = new Sortie();
            $sortieEnCours->setNom($faker->randomElement($nomsSorties));
            $sortieEnCours->setDescription($faker->paragraph(1));
            $sortieEnCours->setDateDebut($randomDate);
            $sortieEnCours->setDuree($faker->numberBetween(60, 240));
            $dateLimiteInscription = (clone $randomDate)->modify('-1 day');
            $sortieEnCours->setDateLimiteInscription($dateLimiteInscription);
            $sortieEnCours->setNbInscritptionMax($faker->numberBetween(1, 20));
            $sortieEnCours->setOrganisateur($faker->randomElement($organisateur));
            $sortieEnCours->setLieu($faker->randomElement($lieux));
            $sortieEnCours->setDateCreation(new DateTimeImmutable());
            $sortieEnCours->setEtat($manager->getRepository(Etat::class)->findbyLibelle('Activité en cours'));

            // Récupérer le campus de l'organisateur
            $campusOrganisateur = $sortieEnCours->getOrganisateur()->getCampus();
            // Attribuer le campus de l'organisateur à la sortie
            $sortieEnCours->setCampus($campusOrganisateur);

            $manager->persist($sortieEnCours);
            $sortiesArray[] = $sortieEnCours;
        }

        // Création de 5 sorties avec un état "Passée"
        for ($i = 0; $i < 5; $i++) {
            // Générer une date aléatoire entre -27 jours et - 1 jour
            $now = new DateTime();
            $startDate = (clone $now)->modify('-27 days');
            $endDate = (clone $now)->modify('-1 day');
            $randomDate = $faker->dateTimeBetween($startDate, $endDate);

            $sortiePassee = new Sortie();
            $sortiePassee->setNom($faker->randomElement($nomsSorties));
            $sortiePassee->setDescription($faker->paragraph(1));
            $sortiePassee->setDateDebut($randomDate);
            $sortiePassee->setDuree($faker->numberBetween(60, 240));
            $dateLimiteInscription = (clone $randomDate)->modify('-1 day');
            $sortiePassee->setDateLimiteInscription($dateLimiteInscription);
            $sortiePassee->setNbInscritptionMax($faker->numberBetween(1, 20));
            $sortiePassee->setOrganisateur($faker->randomElement($organisateur));
            $sortiePassee->setLieu($faker->randomElement($lieux));
            $sortiePassee->setDateCreation(new DateTimeImmutable());
            $sortiePassee->setEtat($manager->getRepository(Etat::class)->findbyLibelle('Passée'));

            // Récupérer le campus de l'organisateur
            $campusOrganisateur = $sortiePassee->getOrganisateur()->getCampus();
            // Attribuer le campus de l'organisateur à la sortie
            $sortiePassee->setCampus($campusOrganisateur);

            $manager->persist($sortiePassee);
            $sortiesArray[] = $sortiePassee;
        }

        // Création de 5 sorties avec un état "Annulée"
        for ($i = 0; $i < 5; $i++) {
            // Générer une date aléatoire entre -27 jours et + 1 mois
            $now = new DateTime();
            $startDate = (clone $now)->modify('-27 days');
            $endDate = (clone $now)->modify('+1 month');
            $randomDate = $faker->dateTimeBetween($startDate, $endDate);

            $sortieAnnulee = new Sortie();
            $sortieAnnulee->setNom($faker->randomElement($nomsSorties));
            $sortieAnnulee->setDescription($faker->paragraph(1));
            $sortieAnnulee->setDateDebut($randomDate);
            $sortieAnnulee->setDuree($faker->numberBetween(60, 240));
            $dateLimiteInscription = (clone $randomDate)->modify('-1 day');
            $sortieAnnulee->setDateLimiteInscription($dateLimiteInscription);
            $sortieAnnulee->setNbInscritptionMax($faker->numberBetween(1, 20));
            $sortieAnnulee->setOrganisateur($faker->randomElement($organisateur));
            $sortieAnnulee->setLieu($faker->randomElement($lieux));
            $sortieAnnulee->setDateCreation(new DateTimeImmutable());
            $sortieAnnulee->setEtat($manager->getRepository(Etat::class)->findbyLibelle('Annulée'));

            // Récupérer le campus de l'organisateur
            $campusOrganisateur = $sortieAnnulee->getOrganisateur()->getCampus();
            // Attribuer le campus de l'organisateur à la sortie
            $sortieAnnulee->setCampus($campusOrganisateur);

            $manager->persist($sortieAnnulee);
            $sortiesArray[] = $sortieAnnulee;
        }

        // Création de 5 sorties avec un état "Archivée"
        for ($i = 0; $i < 5; $i++) {
            // Générer une date aléatoire entre -1 mois et -3 mois
            $now = new DateTime();
            $startDate = (clone $now)->modify('-3 months');
            $endDate = (clone $now)->modify('-1 month');
            $randomDate = $faker->dateTimeBetween($startDate, $endDate);

            $sortieArchivee = new Sortie();
            $sortieArchivee->setNom($faker->randomElement($nomsSorties));
            $sortieArchivee->setDescription($faker->paragraph(1));
            $sortieArchivee->setDateDebut($randomDate);
            $sortieArchivee->setDuree($faker->numberBetween(60, 240));
            $dateLimiteInscription = (clone $randomDate)->modify('-1 day');
            $sortieArchivee->setDateLimiteInscription($dateLimiteInscription);
            $sortieArchivee->setNbInscritptionMax($faker->numberBetween(1, 20));
            $sortieArchivee->setOrganisateur($faker->randomElement($organisateur));
            $sortieArchivee->setLieu($faker->randomElement($lieux));
            $sortieArchivee->setDateCreation(new DateTimeImmutable());
            $sortieArchivee->setEtat($manager->getRepository(Etat::class)->findbyLibelle('Archivée'));

            // Récupérer le campus de l'organisateur
            $campusOrganisateur = $sortieArchivee->getOrganisateur()->getCampus();
            // Attribuer le campus de l'organisateur à la sortie
            $sortieArchivee->setCampus($campusOrganisateur);

            $manager->persist($sortieArchivee);
            $sortiesArray[] = $sortieArchivee;
        }

        $manager->flush();

        $nombreSortiesCreees = count($sortiesArray);
        dump('Nombre de sorties enregistrés : ' . $nombreSortiesCreees);

        //Affectation de participants aux sorties
        // Récupération de toutes les sorties
        $sorties = $manager->getRepository(Sortie::class)->findAll();

		// Compteurs pour le nombre total d'inscrits et le nombre de sorties impactées
        $nombreTotalInscrits = 0;
        $nombreSortiesImpactees = 0;

        foreach ($sorties as $sortie) {
            // Vérifier si l'état de la sortie est différent de 'Créée' car l'inscription n'est pas possible
            if ($sortie->getEtat()->getLibelle() !== 'Créée') {
                // Récupérer tous les participants disponibles (tous sauf l'organisateur de la sortie)
                $participantsDisponibles = $manager->getRepository(Participant::class)->findAll();
                $organisateurSortie = $sortie->getOrganisateur();

                // Supprimer l'organisateur de la liste des participants disponibles
                $participantsDisponibles = array_filter($participantsDisponibles, function ($participant) use ($organisateurSortie) {
                    return $participant !== $organisateurSortie;
                });

                // Vérifier si le nombre maximum d'inscriptions est supérieur au nombre de participants disponibles
                $nombreParticipantsMax = $sortie->getNbInscritptionMax()-1;
                if ($nombreParticipantsMax > count($participantsDisponibles)) {
                    // Si le nombre maximum d'inscriptions est supérieur, ajuster le nombre à la taille du tableau
                    $nombreParticipantsMax = count($participantsDisponibles);
                }
                // Générer un nombre aléatoire d'inscrits entre 0 et le nombre maximum d'inscriptions possible
                $nombreInscrits = $faker->numberBetween(0, $nombreParticipantsMax);
                // Sélectionner un nombre aléatoire de participants disponibles pour la sortie
                $participantsSortie = $faker->randomElements($participantsDisponibles, $nombreInscrits);

                // Affecter les participants sélectionnés à la sortie
                foreach ($participantsSortie as $participant) {
                    $sortie->addParticipant($participant);
                    $manager->persist($participant);
                    $nombreTotalInscrits++; // Incrémenter le compteur du nombre total d'inscrits
                }

                $nombreSortiesImpactees++; // Incrémenter le compteur du nombre de sorties impactées
            }
        }

        dump('Nombre total d\'inscrits : ' . $nombreTotalInscrits);
        dump('Nombre de sorties avec des participants : ' . $nombreSortiesImpactees);

        // Création d'un participant Anonyme pour la désactivation de compte
        $participantAnonyme = new Participant();
        $participantAnonyme->setNom('Anonyme');
        $participantAnonyme->setPrenom('Participant');
        $participantAnonyme->setPassword($this->passwordHasher->hashPassword($participantAnonyme,'azerty'));
        $participantAnonyme->setRoles([]);
        $participantAnonyme->setActif(true);
        $participantAnonyme->setDateCreation(new DateTimeImmutable());
        $participantAnonyme->setCampus($faker->randomElement($campus));
        $participantAnonyme->setEmail('testanonyme@test.com');
        $participantAnonyme->setPseudo('Anonyme');
        $participantAnonyme->setTelephone('0600000000');
        $manager->persist($participantAnonyme);
        $participantsAnonymeArray[] = $participantAnonyme;

        // Affichage du nombre de participants enregistrés
        $nombreParticipantAnonyme = count($participantsAnonymeArray);
        dump($nombreParticipantAnonyme . " participant Anonyme a été créé");

        // Création d'un Administrateur pour la gestion de l'application
        $participantAdmin = new Participant();
        $participantAdmin->setNom('Administrateur');
        $participantAdmin->setPrenom('Participant');
        $participantAdmin->setPassword($this->passwordHasher->hashPassword($participantAdmin,'azerty'));
        $participantAdmin->setRoles(['ROLE_ADMIN']);
        $participantAdmin->setActif(true);
        $participantAdmin->setDateCreation(new DateTimeImmutable());
        $participantAdmin->setCampus($faker->randomElement($campus));
        $participantAdmin->setEmail('participant.administrateur@campus-eni.fr');
        $participantAdmin->setPseudo('Admin');
        $participantAdmin->setTelephone('0600000000');
        $participantAdmin->setPhoto('Photo_Profil_Defaut_Test.png');
        $manager->persist($participantAdmin);
        $administrateurArray[] = $participantAdmin;

        $nombreAdministrateur = count($administrateurArray);
        dump($nombreAdministrateur . " administrateur a été créé");


        $manager->flush();
    }
}