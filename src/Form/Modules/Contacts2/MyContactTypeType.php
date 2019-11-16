<?php

namespace App\Form\Modules\Contacts2;

use App\Controller\Utils\Application;
use App\Entity\Modules\Contacts\MyContactsGroups;
use App\Entity\Modules\Contacts2\MyContactType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyContactTypeType extends AbstractType {

    const KEY_NAME       = 'name';
    const KEY_IMAGE_PATH = 'image_path';
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
                'label' => $this->app->translator->translate('forms.MyContactTypeType.labels.name')
            ])
            ->add(self::KEY_IMAGE_PATH, null, [
                'label' => $this->app->translator->translate('forms.MyContactTypeType.labels.imagePath')
            ])
            ->add(self::KEY_SUBMIT,SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyContactType::class,
        ]);
    }
}
