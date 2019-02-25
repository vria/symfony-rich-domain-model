<?php

namespace App\Application\Controller;

use App\Application\DTO\DeposerAbsenceDTO;
use App\Application\DTO\PersonneCreateDTO;
use App\Application\DTO\PersonneUpdateDTO;
use App\Application\Form\DeposerAbsenceType;
use App\Application\Form\PersonneCreateType;
use App\Application\Form\PersonneUpdateType;
use App\Application\Service\PersonneFactory;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\AbsenceInvalidDatesException;
use App\Domain\Exception\EmailAlreadyTakenException;
use App\Domain\Exception\PersonneNotFoundException;
use App\Domain\Personne;
use App\Domain\Repository\PersonneRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Contrôleur qui contient differentes actions liée à la gestion d'une @see Personne.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneController
{
    /**
     * Lister toutes les personnes.
     *
     * @Route("/", name="person_list")
     * @Template()
     *
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array
     */
    public function listPersonnes(PersonneRepositoryInterface $personneRepository)
    {
        return [
            'personnes' => $personneRepository->getAll()
        ];
    }

    /**
     * Créer une personne.
     *
     * @Route("/create", name="person_create")
     * @Template()
     *
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param PersonneFactory $personneFactory
     *
     * @return array|RedirectResponse
     */
    public function createPersonne(Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneFactory $personneFactory)
    {
        $personneCreteDTO = new PersonneCreateDTO();
        $form = $formFactory->create(PersonneCreateType::class, $personneCreteDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $personneFactory->create($personneCreteDTO);

                return new RedirectResponse($urlGenerator->generate('person_list'));
            } catch (EmailAlreadyTakenException $e) {
                $form->get('email')->addError(new FormError($e->getMessage()));
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Modifier les données d'une personne.
     *
     * @Route("/update/{email}", name="person_update")
     * @Template()
     *
     * @param string $email
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param PersonneFactory $personneFactory
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array|RedirectResponse
     */
    public function updatePersonne(string $email, Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneFactory $personneFactory, PersonneRepositoryInterface $personneRepository)
    {
        try {
            $personne = $personneRepository->get($email);
        } catch (PersonneNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        $personneUpdateDTO = new PersonneUpdateDTO($personne->getEmail(), $personne->getNom());
        $form = $formFactory->create(PersonneUpdateType::class, $personneUpdateDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $personneFactory->update($personne, $personneUpdateDTO);

                return new RedirectResponse($urlGenerator->generate('person_list'));
            } catch (EmailAlreadyTakenException $e) {
                $form->get('email')->addError(new FormError($e->getMessage()));
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Déposer une absence.
     *
     * @Route("/deposer-absence/{email}", name="person_deposer_absence")
     * @Template()
     *
     * @param string $email
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param PersonneFactory $personneFactory
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array|RedirectResponse
     */
    public function deposerAbsence(string $email, Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneFactory $personneFactory, PersonneRepositoryInterface $personneRepository)
    {
        try {
            $personne = $personneRepository->get($email);
        } catch (PersonneNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        $deposerAbsenceDTO = new DeposerAbsenceDTO($personne->getEmail());
        $form = $formFactory->create(DeposerAbsenceType::class, $deposerAbsenceDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $personneFactory->deposerAbsence($personne, $deposerAbsenceDTO);

                return new RedirectResponse($urlGenerator->generate('person_list'));
            } catch (AbsenceInvalidDatesException $e) {
                $form->get('fin')->addError(new FormError($e->getMessage()));
            } catch (AbsenceAlreadyTakenException $e) {
                $form->get('debut')->addError(new FormError($e->getMessage()));
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Déposer une absence.
     *
     * @Route("/absence-calendar/{email}/{startPeriod?}/{endPeriod?}", name="person_deposer_absence")
     * @Template()
     *
     * @param PersonneRepositoryInterface $personneRepository
     * @param string $email
     * @param string $startPeriod
     * @param string $endPeriod
     *
     * @return array
     */
    public function viewAbsenceCalendar(PersonneRepositoryInterface $personneRepository, string $email, string $startPeriod = null, string $endPeriod = null)
    {
        try {
            $personne = $personneRepository->get($email);
        } catch (PersonneNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        $startPeriod = new \DateTimeImmutable($startPeriod ?? 'monday this week');
        $endPeriod = new \DateTimeImmutable($endPeriod ?? 'sunday this week');

        return [
            'personne' => $personne,
            'startPeriod' => $startPeriod,
            'endPeriod' => $endPeriod,
            'absences' => $personne->getAbsences($startPeriod, $endPeriod),
        ];
    }
}
