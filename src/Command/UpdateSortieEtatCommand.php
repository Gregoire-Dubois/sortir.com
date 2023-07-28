<?php

namespace App\Command;

use App\Entity\Sortie;
use App\Event\SortieEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateSortieEtatCommand
 * @package App\Command
 */
class UpdateSortieEtatCommand extends Command
{
    protected static $defaultName = 'update:sortie:etat';
    protected static $defaultDescription = "Commande utilisée pour mettre à jour l'état des sorties";

    /** @var EntityManagerInterface */
    private $em;

    private $logger;

    /** @var SortieEvent */
    private $sortieEtats;


    /**
     * On utilise l'injection de dépendance pour récupérer plein de classes utiles
     *
     * UpdateSortieEtatCommand constructor.
     * @param EntityManagerInterface $em
     * @param SortieEvent $sortieEtats
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        EntityManagerInterface $em,
        SortieEvent            $sortieEtats,
        LoggerInterface        $logger,
        string                 $name = null
    )
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->sortieEtats = $sortieEtats;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription("Met à jour l'état des sorties");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info("MAJ de l'état des sorties débutée");

        $io = new SymfonyStyle($input, $output);

        //On charge toutes les sorties
        //@TODO: Besoin de charger les archivées ?
        $sortieRepo = $this->em->getRepository(Sortie::class);
        $sorties = $sortieRepo->findBy([]);

        $totalModifiees = 0;
        // Boucle sur toutes les sorties pour modifier leur état si nécessaire
        foreach($sorties as $sortie) {

            //Doit-être changé en "Clôturée" ?
            if ($this->sortieEtats->changementEtatSortieCloturee($sortie)){
                //change en "Clôturée"
                $this->sortieEtats->changeEtatSortie($sortie, "Clôturée");
                $totalModifiees++;
                //message pour la console et pour les logs
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en Clôturée";
                //écrit le message dans la console
                $io->writeln($message);
                //puis dans les logs
                $this->logger->info($message);
                continue;

            }

            //Doit-être changé en "Activité en cours" ?
            if ($this->sortieEtats->changementEtatSortieEnCours($sortie)){
                $this->sortieEtats->changeEtatSortie($sortie, "Activité en cours");
                $totalModifiees++;
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en Activité en cours";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }

            //Doit-être changé en "Passée" ?
            if ($this->sortieEtats->changementEtatSortiePassee($sortie)){
                $this->sortieEtats->changeEtatSortie($sortie, "Passée");
                $totalModifiees++;
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en Passée";
                $io->writeln($message);
                $this->logger->info($message);
                continue;
            }

            //Doit-être changé en "Archivée" ?
            if ($this->sortieEtats->changementEtatSortieArchivee($sortie)){
                $this->sortieEtats->changeEtatSortie($sortie, "Archivée");
                $totalModifiees++;
                $message = $sortie->getId() . " " . $sortie->getNom() . " : statut changé en Archivée";
                $io->writeln($message);
                $this->logger->info($message);
            }

        }

        $io->success("OK c'est fait ! " . $totalModifiees . " sorties ont été modifiées.");
        $this->logger->info("MAJ de l'état des sorties terminée");

        return 0;
    }
}
