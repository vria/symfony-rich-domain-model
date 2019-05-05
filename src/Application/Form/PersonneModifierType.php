<?php

namespace App\Application\Form;

use App\Domain\Personne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Formulaire pour modifier une personne.
 *
 * @see \App\Application\Controller\PersonneController::createPerson()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneModifierType extends AbstractType implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'disabled' => true,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->setDataMapper($this)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($personne, $forms)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        /* @var Personne $personne */
        $forms['email']->setData($personne->getEmail());
        $forms['nom']->setData($personne->getNom());
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$personne)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        /* @var Personne $personne */
        $personne->update($forms['nom']->getData());
    }
}
