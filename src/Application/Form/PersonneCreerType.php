<?php

namespace App\Application\Form;

use App\Domain\DTO\PersonneCreerDTO;
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
class PersonneCreerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
        ;
    }
}
