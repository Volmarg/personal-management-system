<?php

namespace App\Form\Modules\Todo;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
use App\Controller\Utils\Utils;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\Module;
use App\Form\Events\Modules\AddRelationToTodoEvent;
use App\Form\Type\RoundcheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyTodoType extends AbstractType
{

    /**
     * If this is provided and contains valid module entity then it will be used as only option in choices
     * and also choices will be hidden in gui
     */
    const OPTION_PREDEFINED_MODULE = "predefined_module";

    const KEY_RELATED_ENTITY_ID    = "relatedEntityId";
    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $predefinedModule = $options[self::OPTION_PREDEFINED_MODULE];

        if( $predefinedModule instanceof EntityInterface ){
            $choices            = [$predefinedModule];
            $isModulePredefined = true;
        }else{
            $choices              = $this->controllers->getModuleController()->getAllActive();
            $isModulePredefined = false;
        }

        $displayOnDashboardId                  = Utils::fieldIdForSymfonyForm($options['data_class'], MyTodo::FIELD_DISPLAY_ON_DASHBOARD);
        $toggleDisplayOnDashboardForModulesIds = json_encode([
            $this->controllers->getModuleController()->getOneByName(ModulesController::MODULE_NAME_GOALS)->getId()
        ]);

        $builder
            ->add(MyTodo::FIELD_NAME, null, [
                'label' => $this->app->translator->translate('forms.MyTodoType.name'),
            ])
            ->add(MyTodo::FIELD_DESCRIPTION, null, [
                'label' => $this->app->translator->translate('forms.MyTodoType.description'),
            ])
            ->add(MyTodo::FIELD_MODULE,EntityType::class,[
                "choices"       => $choices,
                "choice_label"  => function($module){
                    return $module->getName();
                },
                "class"    => Module::class,
                'label'    => $this->app->translator->translate('forms.MyTodoType.module'),
                'required' => $isModulePredefined, // this must be set via variable to force set predefined module,
                "attr"     => [
                    'class'                                          => 'selectpicker',
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                    'data-hide-dom-element'                          => '',
                    'data-hide-dom-element-target-selector'          => '#' . $displayOnDashboardId,
                    'data-hide-dom-element-target-parent-selector'   => '.row',
                    'data-hide-dom-element-for-options-values'       => $toggleDisplayOnDashboardForModulesIds,
                    'data-live-search'                               => 'true',
                ]
            ])
            ->add(MyTodo::FIELD_DISPLAY_ON_DASHBOARD,RoundcheckboxType::class,[
                'label'     => $this->app->translator->translate('forms.MyTodoType.displayOnDashboard'),
                'required'  => false
            ])
            ->add(self::KEY_RELATED_ENTITY_ID, HiddenType::class)
            ->add('submit', SubmitType::class,[
                'label' => $this->app->translator->translate('forms.general.submit'),
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event){
            AddRelationToTodoEvent::postEvent($event, $this->controllers);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyTodo::class,
            self::OPTION_PREDEFINED_MODULE => null,
        ]);
    }
}
