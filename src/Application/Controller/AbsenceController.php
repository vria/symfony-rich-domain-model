<?php

namespace App\Application\Controller;

use App\Application\Form\AbsenceDeposerType;
use App\Domain\DTO\AbsenceDeposerDTO;
use App\Domain\DTO\AbsenceModifierDTO;
use App\Domain\Exception\AbsenceDejaDeposeeException;
use App\Domain\Exception\AbsenceDatesInvalidesException;
use App\Domain\Exception\AbsenceJoursDisponiblesInsuffisantsException;
use App\Domain\Exception\AbsenceNonTrouveeException;
use App\Domain\Exception\PersonneNonTrouveeException;
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
        } catch (PersonneNonTrouveeException $e) {
            throw new NotFoundHttpException();
        }

        if ($startPeriod) {
            $startPeriod = (new \DateTimeImmutable($startPeriod))->modify('monday this week');
        } else {
            $startPeriod = \DateTimeImmutable::createFromFormat('U', time())->modify('monday this week');
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
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array|RedirectResponse
     */
    public function deposer(string $email, Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneRepositoryInterface $personneRepository)
    {
        try {
            $personne = $personneRepository->get($email);
        } catch (PersonneNonTrouveeException $e) {
            throw new NotFoundHttpException();
        }

        $deposerAbsenceDTO = new AbsenceDeposerDTO();
        $form = $formFactory->create(AbsenceDeposerType::class, $deposerAbsenceDTO);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $personne->deposerAbsence($deposerAbsenceDTO);

                return new RedirectResponse($urlGenerator->generate('personne_absence_calendrier', ['email' => $email]));
            } catch (AbsenceDatesInvalidesException | AbsenceDejaDeposeeException | AbsenceJoursDisponiblesInsuffisantsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return [
            'form' => $form->createView(),
            'personne' => $personne,
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
     *
     * @return array|RedirectResponse
     */
    public function modifier(string $email, string $id, Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneRepositoryInterface $personneRepository)
    {
        try {
            $personne = $personneRepository->get($email);
            $absence = $personne->getAbsence($id);
        } catch (PersonneNonTrouveeException | AbsenceNonTrouveeException $e) {
            throw new NotFoundHttpException();
        }

        $modifierAbsenceDTO = AbsenceModifierDTO::fromAbsence($absence);
        $form = $formFactory->create(AbsenceDeposerType::class, $modifierAbsenceDTO);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $personne->modifierAbsence($modifierAbsenceDTO);

                return new RedirectResponse($urlGenerator->generate('personne_absence_calendrier', ['email' => $email]));
            } catch (AbsenceDatesInvalidesException | AbsenceDejaDeposeeException | AbsenceJoursDisponiblesInsuffisantsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return [
            'form' => $form->createView(),
            'personne' => $personne,
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
    public function annuler(string $email, string $id, UrlGeneratorInterface $urlGenerator, PersonneRepositoryInterface $personneRepository)
    {
        try {
            $personne = $personneRepository->get($email);
            $personne->annulerAbsence($id);
        } catch (PersonneNonTrouveeException | AbsenceNonTrouveeException $e) {
            throw new NotFoundHttpException();
        }

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
        } catch (PersonneNonTrouveeException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'personne' => $personne,
            'compteurs' => $personne->getCompteursInfo(),
        ];
    }
}
