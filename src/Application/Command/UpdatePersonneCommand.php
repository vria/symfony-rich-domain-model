<?php

namespace App\Application\Command;

use App\Application\DTO\PersonneUpdateDTO;
use App\Application\Service\PersonneFactory;
use App\Domain\Exception\EmailAlreadyTakenException;
use App\Domain\Exception\PersonneNotFoundException;
use App\Domain\Repository\PersonneRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Modifier les données d'une personne.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class UpdatePersonneCommand extends Command
{
    protected static $defaultName = 'app:personnes:update';

    /**
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @var PersonneFactory
     */
    private $personneFactory;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param PersonneRepositoryInterface $personneRepository
     * @param PersonneFactory $personneFactory
     * @param ValidatorInterface $validator
     */
    public function __construct(PersonneRepositoryInterface $personneRepository, PersonneFactory $personneFactory, ValidatorInterface $validator)
    {
        parent::__construct();

        $this->personneRepository = $personneRepository;
        $this->personneFactory = $personneFactory;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription("Modifier les données d'une personne.")
            ->addArgument('email', InputArgument::REQUIRED);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $personnne = $this->personneRepository->get($input->getArgument('email'));
        } catch (PersonneNotFoundException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return;
        }

        $personneUpdateDTO = new PersonneUpdateDTO($personnne->getEmail(), $personnne->getNom());
        $helper = $this->getHelper('question');

        $question = new Question('Email: ', $personneUpdateDTO->email);
        $personneUpdateDTO->email = $helper->ask($input, $output, $question);

        $question = new Question('Nom: ', $personneUpdateDTO->nom);
        $personneUpdateDTO->nom = $helper->ask($input, $output, $question);

        // Valider la saisie de l'utilisateur
        $constraintViolationList = $this->validator->validate($personneUpdateDTO);
        if ($constraintViolationList->count() > 0) {
            foreach ($constraintViolationList as $violation) {
                /** @var $violation ConstraintViolationInterface */
                $output->writeln(sprintf('<error>%s: %s</error>', $violation->getPropertyPath(), $violation->getMessage()));
            }

            return;
        }

        try {
            $this->personneFactory->update($personnne, $personneUpdateDTO);

            $output->writeln('<info>Personne a été modifiée avec succès.</info>');
        } catch (EmailAlreadyTakenException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
