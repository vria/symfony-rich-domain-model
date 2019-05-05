<?php

namespace App\Application\Form;

use App\Application\DTO\CreerPersonneDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour créer une nouvelle personne.
 *
 * @see \App\Application\Controller\PersonneController::createPerson()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class CreerPersonneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'disabled' => $options['edit'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreerPersonneDTO::class,
            'edit' => false,
        ]);
    }
}
