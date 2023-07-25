<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher=$passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
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

        $manager->persist($participant);

        $manager->flush();
    }
}
