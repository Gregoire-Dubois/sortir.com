<?php


namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortiesFilterType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SortieController extends AbstractController
{
    /**
     * @Route("/", name="sortie_listeSortie")
     */
    public function listeSortie(SortieRepository $sortieRepository, Request $request): Response
    {
        $participantConnnecte = $this->getUser();
        $campusParticipant = $participantConnnecte->getCampus();

        $sortieForm = $this->createForm(SortiesFilterType::class);
        $sortieForm->handleRequest($request);
        $sortiesAll = $sortieRepository->getSortiesCampus($campusParticipant, $participantConnnecte);
        //$data = null;



        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $data = $sortieForm->getData();
         //   dump($data);
            $sortiesAll = $sortieRepository->selectAllSorties($data);
            // dump($sortieForm->getData());

        }
       // $sortiesAll = $sortieRepository->selectAllSorties($data);

        //$sortiesAll = $sortieRepository-> selectAllSorties($data);
        if($sortiesAll==null){
            $this->addFlash('error', 'Il n\'y a pas de sortie correspondant à votre recherche.');
        }
        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sortiesAll,
            'sortieForm' => $sortieForm->createView(),

        ]);

    }

    /**
     * @Route("/sorties/detail/{id}", name="sortie_detailSortie")
     * @throws NonUniqueResultException
     */
    public function detailSortie(int $id, SortieRepository $sortieRepository): Response
    {

        $sortie = $sortieRepository->findSortieByIdWithDetails($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);

    }

    /**
     * @Route("/sorties/creation", name="sortie_creerSortie")
     */
    public function creerSortie(Request $request, EtatRepository $etatRepository, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();

        //On checke l'utilisateur courant
        $campus=$this->getUser()->getCampus();
        //On fixe une date de création
        $sortie->setDateCreation(new \DateTime());
        $sortie->setDateModification(new \DateTime());

        //On paramètre des propositions de date cohérentes dans le formulaire
        $sortie->setDateDebut((new \DateTimeImmutable())->setTime(21, 0));
        $sortie->setDateLimiteInscription($sortie->getDateDebut()->sub(new \DateInterval("PT8H")));
        $sortie->setCampus($campus);
        $sortieType = $this->createForm(SortieType::class, $sortie);

        $sortieType->handleRequest($request);

        if ($sortieType->isSubmitted() && $sortieType->isValid()) {
            //On passe la sortie à l'état Créée si on clique sur créer
            if ($sortieType->getClickedButton() && 'publier' === $sortieType->getClickedButton()->getName()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            } else {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
            }

            //On enregistre le créateur (utilisateur connecté)
            $sortie->setOrganisateur($this->getUser());
            $sortie->setModifiePar($this->getUser());
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a bien été enregistrée');
            return $this->redirectToRoute('sortie_listeSortie');
        }
        $lieuType = $this->createForm(LieuType::class);
        $villeType = $this->createForm(VilleType::class);
        return $this->render('sortie/creation.html.twig', [
            'SortieType' => $sortieType->createView(),
            'LieuType' => $lieuType->createView(),
            'VilleType' => $villeType->createView()
        ]);
    }

    /**
     * @Route("/sorties/modifier/{id}", name="sortie_modifierSortie", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function modifierSortie(int $id, Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, EntityManagerInterface $em): Response
    {
        //Recherche de la sortie concernées avec les jointures idoines
        $sortie = $sortieRepository->findSortieByIdWithDetails($id);
        //On prépare l'alimentation du champ dateModif
        $sortie->setDateModification(new \DateTime());

        //si l'id n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        //On récupère la ville pour l'afficher dans le sélecteur
        $ville = $sortie->getLieu()->getVille();
       // dump($sortie->getEtat());

        //On vérifie l'état de la sortie pour afficher/masque le bouton publier
        if ($sortie->getEtat()->getId() === 1) {
            $etat = true;
        } else {
            $etat = false;
        }

        $sortieType = $this->createForm(SortieType::class, $sortie, [
            'publication_false' => $etat
        ]);

        //On affiche la ville enregistrée en base pour cette sortie
        $sortieType->get('ville')->setData($ville);
        //$sortieType->get('lieu')->setData($lieu);

        $sortieType->handleRequest($request);

        if ($sortieType->isSubmitted() && $sortieType->isValid()) {
            //On passe la sortie à l'état Publiée si on clique sur publier
            if ($sortieType->getClickedButton() && 'publier' === $sortieType->getClickedButton()->getName()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            }

            //On enregistre le créateur (utilisateur connecté)
            $sortie->setModifiePar($this->getUser());
            //$em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La modification de la sortie a bien été enregistrée');
            return $this->redirectToRoute('sortie_listeSortie');
        }
        $lieuType = $this->createForm(LieuType::class);
        $villeType = $this->createForm(VilleType::class);
        return $this->render('sortie/modification.html.twig', [
            'SortieType' => $sortieType->createView(),
            'sortie' => $sortie,
            'LieuType' => $lieuType->createView(),
            'VilleType' => $villeType->createView(),
            'etat' => $etat
        ]);
    }

    /**
     * @Route("/sorties/publier/{id}", name="sortie_publierSortie", methods={"POST"})
     */
    public function publierSortie(int $id, Request $request, SortieRepository $sortieRepository, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {

        $sortie = $sortieRepository->findSortieByIdWithDetails($id);
        //si l'id n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }
        $sortie->setDateModification(new \DateTime());
        $sortie->setModifiePar($this->getUser());
        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
        $em->flush();
        return $this->redirectToRoute('sortie_listeSortie');
    }

    /**
     * @Route("/sorties/annuler/{id}", name="sortie_annulerSortie",  methods={"POST"})
     */
    public function annulerSortie(int $id, Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, EntityManagerInterface $em): Response
    {
        //Recherche de la sortie concernées avec les jointures idoines
        $sortie = $sortieRepository->findSortieByIdWithDetails($id);
        //On prépare l'alimentation du champ dateModif
        $sortie->setDateModification(new \DateTime());

        //si l'id n'existe pas
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }
        $sortieType = $this->createForm(SortieType::class, $sortie, ['only_motif' => true, 'validation_groups' => ['update_motif']]);
        $sortieType->handleRequest($request);

        if ($sortieType->isSubmitted() && $sortieType->isValid()) {
            //On passe la sortie à l'état annulée
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Annulée']));
            //On enregistre le mofif
            //$em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a bien été annulée');
            return $this->redirectToRoute('sortie_listeSortie');
        }
        return $this->render('sortie/annulation.html.twig', [
            'SortieType' => $sortieType->createView(),
            'sortie' => $sortie
        ]);
    }

    /**
     * @Route("/sorties/inscription/{id}", name="sortie_inscription", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function inscriptionSortie(
        Request                   $request,
        Sortie                    $sortie,
        EntityManagerInterface    $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        EtatRepository $etatRepository
    ): Response
    {
        $token = new CsrfToken('inscription_sortie', $request->request->get('_csrf_token'));
        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        //On récupère l'URL qui a émis la requête
        $url = $request->headers->get('referer');
        $participantConnecte = $this->getUser();

        $etatCloture = $etatRepository->findbyLibelle('Clôturée');


        if ($sortie->getEtat()->getLibelle() == 'Ouverte'
            && $sortie->getDateLimiteInscription() >= new \DateTime('now')
            && !$sortie->getParticipants()->contains($participantConnecte)) {

          //  dump($sortie->getEtat()->getLibelle());
            //dump($sortie->getDateLimiteInscription());
            //dump(new \DateTime('now'));

            //dump($sortie);
            //dump($participantConnecte);
            if($participantConnecte instanceof Participant) {
                $sortie->addParticipant($participantConnecte);
              //  dump($sortie->getParticipants());



            //dump(count($sortie->getParticipants()));
                if(count($sortie->getParticipants()) == $sortie->getNbInscritptionMax()){
                    $sortie->setEtat($etatCloture);
                }
                $entityManager->persist($sortie);
                //dump($sortie->getParticipants());
                //dump($participantConnecte);
                $entityManager->flush();
                // dump($sortie->getParticipants());
                //dump($sortie);
                $this->addFlash('success', 'Vous êtes bien inscrit/e pour la sortie ' . $sortie->getNom() . ' !');}


        } else {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire pour la sortie ' . $sortie->getNom() . ' !');
        }


        //On renvoit vers l'URL qui a émis la requête
        if ($url) {
            return $this->redirect($url);
        } else {
            return $this->redirectToRoute('sortie_listeSortie');
        }


    }

    /**
     * @Route("/sorties/desistement/{id}", name="sortie_desistement", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function desistementSortie(
        Request                   $request,
        Sortie                    $sortie,
        EntityManagerInterface    $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        EtatRepository $etatRepository
    ): Response
    {
        $token = new CsrfToken('desistement_sortie', $request->request->get('_csrf_token'));
        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        //On récupère l'URL qui a émis la requête
        $url = $request->headers->get('referer');
        $participantConnecte = $this->getUser();
        $etatOuvert = $etatRepository->findbyLibelle('Ouverte');
        if ($sortie->getDateDebut() >= new \DateTime('now')
            && $sortie->getParticipants()->contains($participantConnecte)) {
          //  dump($sortie->getEtat()->getLibelle());
            //dump($sortie->getDateLimiteInscription());
            //dump(new \DateTime('now'));

            //dump($sortie);
            //dump($participantConnecte);
            if($participantConnecte instanceof Participant) {
                $sortie->removeParticipant($participantConnecte);
                if(count($sortie->getParticipants()) < $sortie->getNbInscritptionMax()){
                    $sortie->setEtat($etatOuvert);
                }

                $entityManager->persist($sortie);
                //dump($participantConnecte);
                $entityManager->flush();
                //dump($sortie);
                $this->addFlash('success', 'Vous êtes bien désinscrit/e pour la sortie ' . $sortie->getNom() . ' !');
            }
        } else {
            $this->addFlash('error', 'Vous ne pouvez pas vous désinscrire pour la sortie ' . $sortie->getNom() . ' !');
        }


        //On renvoit vers l'URL qui a émis la requête ou main_accueil
        if ($url) {
            return $this->redirect($url);
        } else {
            return $this->redirectToRoute('sortie_listeSortie');
        }

    }
}
