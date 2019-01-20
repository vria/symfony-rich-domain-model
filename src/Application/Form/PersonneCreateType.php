<?php

namespace App\Application\Form;

use App\Application\DTO\PersonneCreateDTO;
use App\Domain\Personne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneCreateType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('nom')
        ;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        return [
            'data_class' => PersonneCreateDTO::class,
        ];
    }
}
