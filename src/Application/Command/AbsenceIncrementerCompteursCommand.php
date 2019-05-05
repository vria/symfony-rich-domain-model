<?php

namespace App\Application\Command;

use App\Domain\Personne;
use App\Infrastructure\Doctrine\Repository\PersonneRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceIncrementerCompteursCommand extends Command
{
    protected static $defaultName = 'app:absence:incrementer-compteurs';

    /**
     * @var PersonneRepository
     */
    private $personneRepository;

    /**
     * @param PersonneRepository $personneRepository
     */
    public function __construct(PersonneRepository $personneRepository)
    {
        parent::__construct();

        $this->personneRepository = $personneRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Incrementer des jours travaillés.')
            ->addArgument('date', InputArgument::REQUIRED, 'La date')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $personnes = $this->personneRepository->getAllInfo();

        try {
            $date = new \DateTimeImmutable($input->getArgument('date'));
        } catch (\Exception $e) {
            $output->writeln('<error>Veuillez entrer la date en format valide.</error>');

            return;
        }

        foreach ($personnes as $personne) {
            /* @var $personne Personne */
            $personne->incrementerJoursTravailles($date);
            $this->personneRepository->save($personne);
        }
    }
}
