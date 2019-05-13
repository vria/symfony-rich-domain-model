<?php

namespace App\Application\Command;

use App\Domain\DTO\PersonneCreerDTO;
use App\Domain\Exception\PersonneEmailDejaEnregistreException;
use App\Domain\Factory\PersonneFactory;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Créer une nouvelle personne.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneCreerCommand extends Command
{
    protected static $defaultName = 'app:personne:creer';

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @var AbsenceRepositoryInterface
     */
    private $absenceRepository;

    /**
     * @param ValidatorInterface $validator
     * @param PersonneRepositoryInterface $personneRepository
     * @param AbsenceRepositoryInterface $absenceRepository
     */
    public function __construct(ValidatorInterface $validator, PersonneRepositoryInterface $personneRepository, AbsenceRepositoryInterface $absenceRepository)
    {
        parent::__construct();

        $this->validator = $validator;
        $this->personneRepository = $personneRepository;
        $this->absenceRepository = $absenceRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Créer une nouvelle personne.')
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('nom', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Construire DTO.
        $creerPersonneDTO = new PersonneCreerDTO();
        $creerPersonneDTO->email = $input->getArgument('email');
        $creerPersonneDTO->nom = $input->getArgument('nom');

        // Valider la saisie de l'utilisateur.
        $constraintViolationList = $this->validator->validate($creerPersonneDTO);
        if ($constraintViolationList->count() > 0) {
            foreach ($constraintViolationList as $violation) {
                /* @var $violation ConstraintViolationInterface */
                $output->writeln(sprintf('<error>%s: %s</error>', $violation->getPropertyPath(), $violation->getMessage()));
            }

            return;
        }

        try {
            // Créer une personne.
            $personneFactory = new PersonneFactory($this->personneRepository, $this->absenceRepository);
            $personne = $personneFactory->create($creerPersonneDTO);
            $this->personneRepository->save($personne);

            $output->writeln('<info>Personne a été créée avec succès.</info>');
        } catch (PersonneEmailDejaEnregistreException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
