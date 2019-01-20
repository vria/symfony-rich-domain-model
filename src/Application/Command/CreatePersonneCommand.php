<?php

namespace App\Application\Command;

use App\Application\DTO\PersonneCreateDTO;
use App\Application\Service\PersonneFactory;
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
class CreatePersonneCommand extends Command
{
    protected static $defaultName = 'app:personnes:create';

    /**
     * @var PersonneFactory
     */
    private $personneFactory;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param PersonneFactory $personneFactory
     * @param ValidatorInterface $validator
     */
    public function __construct(PersonneFactory $personneFactory, ValidatorInterface $validator)
    {
        parent::__construct();

        $this->personneFactory = $personneFactory;
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
        $personneCreteDTO = new PersonneCreateDTO();
        $personneCreteDTO->email = $input->getArgument('email');
        $personneCreteDTO->nom = $input->getArgument('nom');

        // Valider la saisie de l'utilisateur
        $constraintViolationList = $this->validator->validate($personneCreteDTO);
        if ($constraintViolationList->count() > 0) {
            foreach ($constraintViolationList as $violation) {
                /** @var $violation ConstraintViolationInterface */
                $output->writeln(sprintf('<error>%s: %s</error>', $violation->getPropertyPath(), $violation->getMessage()));
            }

            return;
        }

        try {
            $this->personneFactory->create($personneCreteDTO);

            $output->writeln('<info>Personne a été créée avec succès.</info>');
        } catch (EmailAlreadyTakenException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
