<?php
namespace Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
class UsersType extends AbstractType
{
    public function __construct()
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_id', 'integer')
            ->add('username', 'text', array(
                'required' => true
            ))
            ->add('passsword', 'password', array(
                'required' => true
            ))
            ->add('submit', 'submit')
        ;
        // $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        //     error_log(print_r('PRE_SET_DATA',1).' '.__FILE__.' '.__LINE__.PHP_EOL,0);
        //     $data = $event->getData();
        //     $form = $event->getForm();
        // });
        // $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
        //     error_log(print_r('POST_SET_DATA',1).' '.__FILE__.' '.__LINE__.PHP_EOL,0);
        //     $data = $event->getData();
        //     $form = $event->getForm();
        // });
        // $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
        //     error_log(print_r('PRE_SUBMIT',1).' '.__FILE__.' '.__LINE__.PHP_EOL,0);
        //     $data = $event->getData();
        //     $form = $event->getForm();
        // });
        // $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
        //     error_log(print_r('SUBMIT',1).' '.__FILE__.' '.__LINE__.PHP_EOL,0);
        //     $data = $event->getData();
        //     $form = $event->getForm();
        // });
        // $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
        //     error_log(print_r('POST_SUBMIT',1).' '.__FILE__.' '.__LINE__.PHP_EOL,0);
        //     $data = $event->getData();
        //     $form = $event->getForm();
        // });
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Form',
        ));
    }
    public function getName()
    {
        return 'app_todostype';
    }
}
