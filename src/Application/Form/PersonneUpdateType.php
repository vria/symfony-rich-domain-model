<?php

namespace App\Application\Form;

use App\Application\DTO\PersonneCreateDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneUpdateType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('nom');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        return [
            'data_class' => PersonneCreateDTO::class
        ];
    }
}
