<?php

namespace App\Form\Modules\Contacts;

use App\Controller\Core\Application;
use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Contacts\MyContactGroup;
use App\Form\Type\JscolorpickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyContactType extends AbstractType {

    const KEY_ID                            = "id";
    const KEY_NAME                          = 'name';
    const KEY_DESCRIPTION                   = 'description';
    const KEY_IMAGE_PATH                    = 'image_path';
    const KEY_NAME_BACKGROUND_COLOR         = 'name_background_color';
    const KEY_DESCRIPTION_BACKGROUND_COLOR  = 'description_background_color';
    const KEY_SUBMIT                        = 'submit';
    const KEY_GROUP                         = "group";

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        // todo: contacts field is invisible so it has to be validated additionally on front + back

        $builder
            ->add(self::KEY_ID, HiddenType::class)
            ->add(self::KEY_NAME, TextType::class, [
                'label' => $this->app->translator->translate('forms.MyContactType.labels.' . self::KEY_NAME)
            ])
            ->add(self::KEY_DESCRIPTION,TextType::class, [
                'label' => $this->app->translator->translate('forms.MyContactType.labels.' . self::KEY_DESCRIPTION)
            ])
            ->add(self::KEY_IMAGE_PATH, TextType::class, [
                'label'    => $this->app->translator->translate('forms.MyContactType.labels.' . self::KEY_IMAGE_PATH),
                'required' => false,
            ])
            ->add(self::KEY_GROUP,EntityType::class, [
                'class'         => MyContactGroup::class,
                'choices'       => $this->app->repositories->myContactGroupRepository->getAllNotDeleted(),
                'choice_label'  => function (MyContactGroup $contact_group) {
                    return $contact_group->getName();
                },
                'label' => $this->app->translator->translate('forms.MyContactType.labels.' . self::KEY_GROUP),
            ])
            ->add(self::KEY_NAME_BACKGROUND_COLOR, JscolorpickerType::class, [
                'attr' => [
                    'style' => 'height:40px !important; width:80px !important;'
                ],
                'label' => $this->app->translator->translate('forms.MyContactType.labels.' . self::KEY_NAME_BACKGROUND_COLOR)
            ])
            ->add(self::KEY_DESCRIPTION_BACKGROUND_COLOR, JscolorpickerType::class, [
                'attr' => [
                    'style' => 'height:40px !important; width:80px !important;'
                ],
                'label' => $this->app->translator->translate('forms.MyContactType.labels.' . self::KEY_DESCRIPTION_BACKGROUND_COLOR)
            ])
            ->add(self::KEY_SUBMIT, SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class'         => MyContact::class,
            'allow_extra_fields' => true,
        ]);
    }
}
