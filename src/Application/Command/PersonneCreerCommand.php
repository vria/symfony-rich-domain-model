<?php

namespace App\Application\Command;

use App\Application\DTO\CreerPersonneDTO;
use App\Application\Service\PersonneService;
use App\Domain\Exception\EmailAlreadyTakenException;
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
     * @var PersonneService
     */
    private $personneService;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param PersonneService $personneService
     * @param ValidatorInterface $validator
     */
    public function __construct(PersonneService $personneService, ValidatorInterface $validator)
    {
        parent::__construct();

        $this->personneService = $personneService;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Créer une nouvelle personne.')
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('nom', InputArgument::REQUIRED);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Construire DTO.
        $creerPersonneDTO = new CreerPersonneDTO();
        $creerPersonneDTO->email = $input->getArgument('email');
        $creerPersonneDTO->nom = $input->getArgument('nom');

        // Valider la saisie de l'utilisateur.
        $constraintViolationList = $this->validator->validate($creerPersonneDTO);
        if ($constraintViolationList->count() > 0) {
            foreach ($constraintViolationList as $violation) {
                /** @var $violation ConstraintViolationInterface */
                $output->writeln(sprintf('<error>%s: %s</error>', $violation->getPropertyPath(), $violation->getMessage()));
            }

            return;
        }

        try {
            // Créer une personne.
            $this->personneService->create($creerPersonneDTO);

            $output->writeln('<info>Personne a été créée avec succès.</info>');
        } catch (EmailAlreadyTakenException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
