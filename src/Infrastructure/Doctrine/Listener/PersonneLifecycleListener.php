<?php

namespace App\Infrastructure\Doctrine\Listener;

use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;
use App\Domain\Service\AbsenceCompteurService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneLifecycleListener implements EventSubscriber
{
    /**
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @var AbsenceRepositoryInterface
     */
    private $absenceRepository;

    /**
     * @param PersonneRepositoryInterface $personneRepository
     * @param AbsenceRepositoryInterface  $absenceRepository
     */
    public function __construct(PersonneRepositoryInterface $personneRepository, AbsenceRepositoryInterface $absenceRepository)
    {
        $this->personneRepository = $personneRepository;
        $this->absenceRepository = $absenceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    /**
     * @var LifecycleEventArgs
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $personne = $event->getEntity();
        if (!$personne instanceof Personne) {
            return;
        }

        $reflProp = (new \ReflectionClass(Personne::class))->getProperty('personneRepository');
        $reflProp->setAccessible(true);
        $reflProp->setValue($personne, $this->personneRepository);

        $reflProp = (new \ReflectionClass(Personne::class))->getProperty('absenceRepository');
        $reflProp->setAccessible(true);
        $reflProp->setValue($personne, $this->absenceRepository);

        $reflProp = (new \ReflectionClass(Personne::class))->getProperty('absenceCompteurService');
        $reflProp->setAccessible(true);
        $reflProp->setValue($personne, new AbsenceCompteurService($this->absenceRepository));
    }
}
