<?php

namespace App\Form\Modules\Contacts;

use App\Controller\Core\Application;
use App\Entity\Modules\Contacts\MyContactType;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This form is called as subform in the "createContactCard" widget for contacts
 * It does not have submit on purpose
 * Class MyContactTypeDtoType
 * @package App\Form\Modules\Contacts
 */
class MyContactTypeDtoType extends AbstractType {

    const KEY_NAME = 'name';  // todo: change to details
    const KEY_TYPE = 'type';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $name_value      = '';
        $selected_entity = null;

        if( array_key_exists(self::KEY_NAME, $options)){
            $name_value = $options[self::KEY_NAME];
        }

        if( array_key_exists(self::KEY_TYPE, $options)){
            $type_name = $options[self::KEY_TYPE];
            $selected_entity = $this->app->repositories->myContactTypeRepository->getOneNonDeletedByName($type_name);
        }

        $builder
            ->add(self::KEY_NAME, null, [
                'label' => $this->app->translator->translate('forms.MyContactTypeDtoType.labels.' . self::KEY_NAME),
                "attr"  => [
                    'value' => $name_value
                ]
            ])
            ->add(self::KEY_TYPE,EntityType::class, [
                'class'         => MyContactType::class,
                'choices'       => $this->app->repositories->myContactTypeRepository->getAllNotDeleted(),
                'choice_label'  => function (MyContactType $contact_type) {
                    return $contact_type->getName();
                },
                'label' => $this->app->translator->translate('forms.MyContactTypeDtoType.labels.' . self::KEY_TYPE),
                'data'  => $selected_entity,
                'attr'  => [
                    "class" => "selectpicker"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::KEY_NAME);
        $resolver->setDefined(self::KEY_TYPE);
    }

    /**
     * @return UuidInterface|string
     * @throws Exception
     */
    public function getBlockPrefix(){
        // each form name must be unique
        // TODO: returns only number
        return Uuid::uuid1();
    }
}
