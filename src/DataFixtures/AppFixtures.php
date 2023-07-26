<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
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

        //création de 10 participants
        $participant = new Participant();
        $participant->setNom('NomTest');
        $participant->setPrenom('prenomTest');
        $participant->setEmail('test@test.com');
        $participant->setPassword($this->passwordHasher->hashPassword($participant,'azerty'));
        $participant->setRoles(['ROLE_PARTICIPANT']);
        $participant->setActif(true);
        $participant->setDateCreation(new \DateTimeImmutable());
        $participant->setPseudo('PseudoTest');
        $participant->setTelephone('0600010203');
        $participant->setCampus($campusArray[0]);
        $participant->setPhoto('testupload-64c152eec147d.jpg');
        $manager->persist($participant);

        $faker = Faker\Factory::create('fr_FR');

        for($i=0;$i<10;$i++)
        {
            $participant = new Participant();
            $participant->setNom($faker->lastName());
            $participant->setPrenom($faker->firstName());
            $participant->setEmail($faker->email());
            $participant->setPassword($this->passwordHasher->hashPassword($participant,'azerty'));
            $participant->setRoles(['ROLE_PARTICIPANT']);
            $participant->setActif(true);
            $participant->setDateCreation(new \DateTimeImmutable());
            $participant->setPseudo($participant->getPrenom().'_'.$participant->getNom());
            $participant->setTelephone($faker->phoneNumber());
            $participant->setCampus($campusArray[array_rand($campusArray)]);
            $participant->setPhoto('Photo_Profil_Defaut_Test.png');
            $manager->persist($participant);
        }

        //Création des 20 plus grandes villes de France
        $villesData =[
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

        foreach ($villesData as $villeData) {
            $ville = new Ville();
            $ville->setNom($villeData['nom']);
            $ville->setCodePostal($villeData['codePostal']);
            $manager->persist($ville);
        }

        $manager->flush();

        //Création de 20 lieux avec Faker
        $villes = $manager->getRepository(Ville::class)->findAll();

        for ($i = 1; $i <= 20; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);
            $lieu->setVille($faker->randomElement($villes));
            $manager->persist($lieu);
        }

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

        foreach ($nomsEtats as $nomEtat) {
            $etat = new Etat();
            $etat->setLibelle($nomEtat);
            $manager->persist($etat);
        }

        $manager->flush();

        $nomsSorties = ['Bowling', 'Bar', 'Poker', 'Concert', 'Cinéma',
            			'Randonnée', 'Restaurant', 'Soirée', 'Escape Game',
            			'Chasse au Trésor', 'Blind Test', "Visite d'une Brasserie",
            			"Parc d'Attraction", 'Billard', 'Virtual Reality', 'Camping',
            			'Festival', 'Exposition', 'Theatre', 'Zoo'];
        $lieux = $manager->getRepository(Lieu::class)->findAll();
        $etats = $manager->getRepository(Etat::class)->findAll();
        $organisateur = $manager->getRepository(Participant::class)->findAll();

        // Création de 20 sorties aléatoires
        for ($i = 0; $i < 20; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->randomElement($nomsSorties));
            $sortie->setDescription($faker->paragraph(1));
            $sortie->setDateDebut($faker->dateTimeThisMonth());
            $sortie->setDuree($faker->numberBetween(60, 240));
            $sortie->setDateLimiteInscription($faker->dateTimeThisMonth());
            $sortie->setNbInscritptionMax($faker->numberBetween(2, 20));
            $sortie->setCampus($faker->randomElement($campusArray));
            $sortie->setEtat($faker->randomElement($etats));
            $sortie->setLieu($faker->randomElement($lieux));
            $sortie->setOrganisateur($faker->randomElement($organisateur));
            $sortie->setDateCreation(new \DateTimeImmutable());

            $manager->persist($sortie);
        }

        $manager->flush();
    }
}