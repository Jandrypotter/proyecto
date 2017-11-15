<?php

namespace ACC\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('firstName')
            ->add('lastName')
            ->add('email',  EmailType::class)
            ->add('password', PasswordType::class)
            ->add('role', ChoiceType::class, array('choices'=> array('Administrador' => 'ROLE_ADMIN',
                'Usuario' => 'ROLE_USER'), 'placeholder' => 'Selecciona un rol'))
            ->add('isActive', CheckboxType::class)
            ->add('save', SubmitType::class, array('label' => 'Save User'))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ACC\UserBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'acc_userbundle_user';
    }


}
