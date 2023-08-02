<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    /**
     * @Route("/sorties/ville/afficher", name="ville_afficher", methods={"GET"})
     */

    //Sert à l'affichage du formulaire dans la pop-up
    public function create(Request $request, EntityManagerInterface $em)
    {
        // Créer une instance du formulaire LieuType
        $ville = new Ville();
        $villeType = $this->createForm(VilleType::class, $ville);

        return $this->render('ville/creation.html.twig', [
            'VilleType' => $villeType->createView()
        ]);
    }


    /**
     * @Route("/sorties/ville/creation", name="ville_creation_submit", methods={"POST"})
     */
    public function createCity(Request $request, EntityManagerInterface $em)
    {
        // Créer une instance du formulaire VilleType
        $ville = new Ville();
        $villeType = $this->createForm(VilleType::class, $ville);

        // Gérer la soumission du formulaire
        $villeType->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($villeType->isSubmitted() && $villeType->isValid()) {
            // Enregistrer les données en base de données
            $em->persist($ville);
            $em->flush();

            // Réponse JSON pour indiquer le succès de l'enregistrement
            return $this->json([
                'success' => true,
                'message' => 'La ville a été créée avec succès!',
                'id' => $ville->getId(),
                'nom' => $ville->getNom()
            ]);
        } else {
            // Réponse JSON pour renvoyer les erreurs en cas d'échec de validation du formulaire
            $errors = $this->getFormErrors($villeType);
            return $this->json([
                'success' => false,
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}