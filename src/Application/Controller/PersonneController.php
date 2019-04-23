<?php

namespace App\Application\Controller;

use App\Application\DTO\CreerPersonneDTO;
use App\Application\Form\CreerPersonneType;
use App\Application\Service\PersonneService;
use App\Domain\Exception\PersonneEmailAlreadyTakenException;
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
     * @Route("/", name="personne_lister")
     * @Template()
     *
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array
     */
    public function lister(PersonneRepositoryInterface $personneRepository)
    {
        return [
            'personnes' => $personneRepository->getAllInfo()
        ];
    }

    /**
     * Créer une personne.
     *
     * @Route("/personne", name="personne_creer")
     * @Template()
     *
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param PersonneService $personneFactory
     *
     * @return array|RedirectResponse
     */
    public function creer(Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneService $personneFactory)
    {
        $creerPersonneDTO = new CreerPersonneDTO();
        $form = $formFactory->create(CreerPersonneType::class, $creerPersonneDTO);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $personneFactory->create($creerPersonneDTO);

                return new RedirectResponse($urlGenerator->generate('personne_lister'));
            } catch (PersonneEmailAlreadyTakenException $e) {
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
     * @Route("/personne/{email}", name="personne_modifier")
     * @Template()
     *
     * @param string $email
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param PersonneService $personneFactory
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @return array|RedirectResponse
     */
    public function modifier(string $email, Request $request, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, PersonneService $personneFactory, PersonneRepositoryInterface $personneRepository) {
        try {
            $personne = $personneRepository->get($email);
        } catch (PersonneNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        $modifierPersonneDTO = CreerPersonneDTO::fromPerson($personne);
        $form = $formFactory->create(CreerPersonneType::class, $modifierPersonneDTO, [
            'edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personneFactory->update($personne, $modifierPersonneDTO);

            return new RedirectResponse($urlGenerator->generate('personne_lister'));
        }

        return [
            'form' => $form->createView()
        ];
    }
}
