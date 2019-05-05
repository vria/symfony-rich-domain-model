<?php

namespace App\Application\Controller;

use App\Application\DTO\DeposerAbsenceDTO;
use App\Application\DTO\ModifierAbsenceDTO;
use App\Application\Form\DeposerAbsenceType;
use App\Application\Service\PersonneService;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\AbsenceDatesInvalidesException;
use App\Domain\Exception\AbsenceJoursDisponiblesInsuffisantsException;
use App\Domain\Exception\AbsenceNotFoundException;
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
class AbsenceController
{
    /**
     * Déposer une absence.
     *
     * @Route("/absence/calendrier/{email}/{startPeriod?}", name="personne_absence_calendrier")
     * @Template()
     *
     * @param PersonneRepositoryInterface $personneRepository
     * @param string                      $email
     * @param string                      $startPeriod
     * @param string                      $endPeriod
     *
     * @return array
     */
    public function calendrier(PersonneRepositoryInterface $personneRepository, string $email, string $startPeriod = null, string $endPeriod = null)
    {
        try {
            $personne = $personneRepository->get($email);
        } catch (PersonneNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        if ($startPeriod) {
            $startPeriod = (new \DateTimeImmutable($startPeriod))->modify('monday this week');
        } else {
            $startPeriod = (\DateTimeImmutable::createFromFormat('U', time()))->modify('monday this week');
        }

        $endPeriod = $startPeriod->modify('sunday this week');

        return [
            'personne' => $personne,
            'startPeriod' => $startPeriod,
            'endPeriod' => $endPeriod,
        ];
    }

    /**
     * Déposer une absence.
     *
     * @Route("/absence/deposer/{email}", name="personne_absence_deposer")
     * @Template()
     *
     * @param string                      $email
     * @param Request                     $request
     * @param FormFactoryInterface        $formFactory
     * @param UrlGeneratorInterface       $urlGenerator
     * @param PersonneService             $personneService
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array|RedirectResponse
     */
    public function deposer(string $email, Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneService $personneService, PersonneRepositoryInterface $personneRepository)
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
                $personneService->deposerAbsence($personne, $deposerAbsenceDTO);

                return new RedirectResponse($urlGenerator->generate('personne_absence_calendrier', ['email' => $email]));
            } catch (AbsenceDatesInvalidesException | AbsenceAlreadyTakenException | AbsenceJoursDisponiblesInsuffisantsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/absence/modifier/{email}/{id}", name="personne_absence_modifier")
     * @Template()
     *
     * @param string                      $email
     * @param string                      $id
     * @param Request                     $request
     * @param FormFactoryInterface        $formFactory
     * @param UrlGeneratorInterface       $urlGenerator
     * @param PersonneRepositoryInterface $personneRepository
     * @param PersonneService             $personneFactory
     *
     * @return array|RedirectResponse
     */
    public function modifier(string $email, string $id, Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneRepositoryInterface $personneRepository, PersonneService $personneFactory)
    {
        try {
            $personne = $personneRepository->get($email);
            $absence = $personne->getAbsence($id);
        } catch (PersonneNotFoundException | AbsenceNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        $modifierAbsenceDTO = ModifierAbsenceDTO::fromAbsence($personne->getEmail(), $absence);
        $form = $formFactory->create(DeposerAbsenceType::class, $modifierAbsenceDTO);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $personneFactory->modifierAbsence($personne, $modifierAbsenceDTO);

                return new RedirectResponse($urlGenerator->generate('personne_absence_calendrier', ['email' => $email]));
            } catch (AbsenceDatesInvalidesException | AbsenceAlreadyTakenException | AbsenceJoursDisponiblesInsuffisantsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/absence/annuler/{email}/{id}", name="personne_absence_annuler")
     *
     * @param string                      $email
     * @param string                      $id
     * @param UrlGeneratorInterface       $urlGenerator
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array|RedirectResponse
     */
    public function supprimer(string $email, string $id, UrlGeneratorInterface $urlGenerator, PersonneRepositoryInterface $personneRepository)
    {
        try {
            $personne = $personneRepository->get($email);
            $absence = $personne->getAbsence($id);
        } catch (PersonneNotFoundException | AbsenceNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        $personne->annulerAbsence($absence);

        return new RedirectResponse($urlGenerator->generate('personne_absence_calendrier', ['email' => $email]));
    }

    /**
     * @Route("/compteurs/jours_disponibles/{email}", name="personne_compteurs_jours_disponibles")
     * @Template()
     *
     * @param string                      $email
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array|RedirectResponse
     */
    public function compteurs(string $email, PersonneRepositoryInterface $personneRepository)
    {
        try {
            $personne = $personneRepository->get($email);
        } catch (PersonneNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'personne' => $personne,
            'compteurs' => $personne->getCompteursInfo(),
        ];
    }
}
