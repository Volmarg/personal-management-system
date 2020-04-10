<?php

namespace App\Form\Modules\Contacts;

use App\Controller\Core\Application;
use App\Entity\Modules\Contacts\MyContactGroup;
use App\Form\Type\FontawesomepickerType;
use App\Form\Type\JscolorpickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyContactGroupType extends AbstractType {

    const KEY_NAME       = 'name';
    const KEY_ICON       = 'icon';
    const KEY_COLOR      = 'color';
    const KEY_SUBMIT     = "submit";

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(self::KEY_NAME, null, [
                'label' => $this->app->translator->translate('forms.MyContactGroupType.labels.name')
            ])
            ->add(self::KEY_ICON, FontawesomepickerType::class, [
                'label' => $this->app->translator->translate('forms.MyContactGroupType.labels.icon')
            ])
            ->add(self::KEY_COLOR, JscolorpickerType::class, [
                'attr' => [
                    'style' => 'height:40px !important; width:80px !important;'
                ],
                'label' => $this->app->translator->translate('forms.MyContactGroupType.labels.color')
            ])
            ->add(self::KEY_SUBMIT,SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyContactGroup::class,
        ]);
    }
}