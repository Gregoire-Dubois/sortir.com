<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
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
        $nomsCampus = ['SAINT HERBLAIN', 'CHARTRES DE BRETAGNE', 'LA ROCHE SUR YON'];

        $campusArray = [];

        foreach ($nomsCampus as $nomCampus){
            $campus = new Campus();
            $campus->setNom($nomCampus);
            $manager->persist($campus);
            $campusArray[] = $campus;
        }

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
        $manager->persist($participant);

        $faker = Faker\Factory::create('fr_FR');

        //créaton de 10 particpants
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
            $participant->setCampus($campusArray[array_rand($campusArray)]); //pour choisir aleatoirement dans campusArray
            $manager->persist($participant);
        }

        $manager->flush();
    }
}
