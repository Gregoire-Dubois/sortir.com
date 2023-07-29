<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Geolocalisation\MapBox;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/sorties/lieu/creation", name="lieu_creation")
     */
    public function create(Request $request, MapBox $mapBox, VilleRepository $villeRepository, EntityManagerInterface $em) : Response
    {
        $lieu = New Lieu();
        $lieuType = $this->createForm(LieuType::class, $lieu);
        $lieuType->handleRequest($request);

        if ($lieuType-> isSubmitted() && $lieuType->isValid()) {
            $em->persist($lieu);
            $em->flush();
        }
        return $this->render('lieu/creation.html.twig', [
            'LieuType' => $lieuType->createView()
        ]);
    }

    /**
     * Méthode appelée en AJAX pour retourner le CP d'une ville donnée.
     * @Route("/get-code-postal/{id}", name="get_cp_par_ville", methods={"GET"})
     */
    public function findCPParVille(Ville $ville) : JsonResponse
    {
        $codePostal = $ville->getCodePostal();

        return new JsonResponse($codePostal);
    }

    /**
     * Méthode appelée en AJAX pour retourner la rue d'un lieu donné.
     * @Route("/get-rue/{id}", name="get_rue_lieu", methods={"GET"})
     */
    public function findStreetByLocation(Lieu $lieu) : JsonResponse
    {
        $rue = $lieu->getRue();

        return new JsonResponse($rue);
    }
}
