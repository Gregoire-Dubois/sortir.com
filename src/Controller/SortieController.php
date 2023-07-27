<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use \Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties", name="sortie_listeSortie")
     */
    public function listeSortie(): Response
    {
        return $this->render('sortie/liste.html.twig', [

        ]);
    }

    /**
     * @Route("/sorties/detail/{id}", name="sortie_detailSortie")
     */
    public function detailSortie(int $id): Response
    {
        return $this->render('sortie/detail.html.twig', [

        ]);
    }

    /**
     * @Route("/sorties/creation", name="sortie_creerSortie")
     */
    public function creerSortie(Request $request, EtatRepository $etatRepository, EntityManagerInterface $em): Response
    {
        $sortie = New Sortie();
        //On fixe une date de création
        $sortie->setDateCreation(new \DateTime());
        $sortie->setDateModification(new \DateTime());

        //On paramètre des propositions de date cohérentes dans le formulaire
        $sortie->setDateDebut((new \DateTimeImmutable())->setTime(21,0));
        $sortie->setDateLimiteInscription($sortie->getDateDebut()->sub(new \DateInterval("PT8H")));

        $sortieType = $this->createForm(SortieType::class, $sortie);

        $sortieType->handleRequest($request);

        if ($sortieType-> isSubmitted() && $sortieType->isValid()){
            //On passe la sortie à l'état Créée
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));

            //On enregistre le créateur (utilisateur connecté)
            $sortie->setOrganisateur($this->getUser());
            $sortie->setModifiePar($this->getUser());
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a bien été enregistrée');
            //return $this->redirectToRoute('sortie_listeSortie');
        }
        return $this->render('sortie/creation.html.twig', [
            'SortieType' => $sortieType->createView()
        ]);
    }

    /**
     * @Route("/sorties/publier/{id}", name="sortie_publierSortie")
     */
    public function publierSortie(int $id): Response
    {
        return $this->redirectToRoute('sortie_listeSortie');
    }

    /**
     * @Route("/sorties/annuler/{id}", name="sortie_annulerSortie")
     */
    public function annulerSortie(int $id): Response
    {
        return $this->redirectToRoute('sortie_listeSortie');
    }
}
