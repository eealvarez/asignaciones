<?php

namespace INFUNISA\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//agrego las siguientes líneas de TextType. Estas importaciones de clases debido a que es requerido para usar formularios en Symfony3:
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
                //Así era en Symfony 2.7.3:

            //->add('email', 'email') //tipo de campo: email
            //->add('password', 'password')

            //pero ahora en Symfony 3.4.15 es:
            ->add('email', EmailType::class) //tipo de campo: email
            ->add('password', PasswordType::class)
            ->add('role', ChoiceType::class, array('choices' => array('Administrator' => 'ROLE_ADMIN' , 'User' => 'ROLE_USER'), 'placeholder' => 'Select a role'))
            ->add('isActive', CheckboxType::class)
           // ->add('createdAt') //Estos campos no necesitamos definirlos porque los vamos a definir más adelante, ya que estos campos no los vamos a renderizar en el formulario
           // ->add('updatedAt') //Queremos que estos dos campos se generen de forma automática
            ->add('save', SubmitType::class, array('label' => 'Save user'))
                ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'INFUNISA\UserBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
         return 'user';
       // return 'infunisa_userbundle_user';    //originalmente se generó así, pero también se puede solo escribir el nombre del formulario que va a tomar
    }


}
