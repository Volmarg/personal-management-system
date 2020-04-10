<?php

namespace App\Form\Modules\Passwords;

use App\Controller\Core\Application;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPasswordsGroupsType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => $this->app->translator->translate('forms.MyPasswordsGroupsType.labels.name')
            ])
            ->add('submit',SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyPasswordsGroups::class,
        ]);
    }
}
