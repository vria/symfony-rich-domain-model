<?php

namespace App\Application\Command;

use App\Domain\Exception\PersonneEmailDejaEnregistreException;
use App\Domain\Exception\PersonneNonTrouveeException;
use App\Domain\Personne;
use App\Domain\Repository\PersonneRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Modifier les données d'une personne.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneModifierCommand extends Command
{
    protected static $defaultName = 'app:personne:modifier';

    /**
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param PersonneRepositoryInterface $personneRepository
     * @param ValidatorInterface          $validator
     */
    public function __construct(PersonneRepositoryInterface $personneRepository, ValidatorInterface $validator)
    {
        parent::__construct();

        $this->personneRepository = $personneRepository;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription("Modifier les données d'une personne.")
            ->addArgument('email', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Récupérer une personne demandée.
            $personne = $this->personneRepository->get($input->getArgument('email'));
            /** @var Personne $personne */
        } catch (PersonneNonTrouveeException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return;
        }

        // Demander l'utilisateur à entrer les nouvelles valeurs.
        $helper = $this->getHelper('question');

        $question = new Question('Nom: ', $personne->getNom());
        $nom = $helper->ask($input, $output, $question);

        $this->validator->validate($nom, [
            new Assert\NotBlank(),
        ]);

        try {
            // Modifier une personne.
            $personne->renommer($nom);
            $this->personneRepository->save($personne);

            $output->writeln('<info>Personne a été modifiée avec succès.</info>');
        } catch (PersonneEmailDejaEnregistreException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
