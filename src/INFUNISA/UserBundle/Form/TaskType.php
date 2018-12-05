<?php

namespace INFUNISA\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;        //importamos este objeto porque necesitamos mostrar los usuarios que queres asignar a las tareas. EntityRepository para recuperar los usuarios de la BD

//agrego las siguientes líneas de TextType. Estas importaciones de clases debido a que es requerido para usar formularios en Symfony3:
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TaskType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('title')
                ->add('description')
                
                //Así era en Symfony 2.7.3:
                
                //->add('user', 'entity', array(    //entity, para referirnos a una entidad cuando estemos representando este campo en el formulario
                
                //pero ahora en Symfony 3.4.15 es:
                ->add('user', EntityType::class, array(
                    'class' => 'INFUNISAUserBundle:User',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                ->where('u.role = :only')
                                ->setParameter('only', 'ROLE_USER');
                    },
                            //prmero creamos una propiedad dentro de User.php, para indicarle que concatene el firstName y lastName
                            'choice_label' => 'getFullName'
                ))
                            //estos campos los generó automáticamente el generador de formularios cuando lo creé
//                ->add('status')
//                ->add('createdAt')
//                ->add('updatedAt')
//                ->add('user');
                ->add('save', SubmitType::class, array('label' => 'Save task'));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'INFUNISA\UserBundle\Entity\Task'
        ));
    }

    //esto lo generó automáticamente el generador de formularios cuando lo creé
//    /**
//     * {@inheritdoc}
//     */
//    public function getBlockPrefix()
//    {
//        return 'infunisa_userbundle_task';
//    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'task';
    }


}
