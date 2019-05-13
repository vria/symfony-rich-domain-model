<?php

namespace App\Application\Form;

use App\Domain\AbsenceType;
use App\Domain\DTO\AbsenceDeposerDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour déposer une nouvelle absence.
 *
 * @see \App\Application\Controller\PersonneController::deposerAbsence()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceDeposerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debut', DateType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('fin', DateType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Maladie' => AbsenceType::MALADIE,
                    'Congé payé' => AbsenceType::CONGES_PAYES,
                    'Télétravail' => AbsenceType::TELETRAVAIL,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        return [
            'data_class' => AbsenceDeposerDTO::class,
        ];
    }
}
