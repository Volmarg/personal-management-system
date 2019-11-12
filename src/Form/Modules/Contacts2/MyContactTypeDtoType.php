<?php

namespace App\Form\Modules\Contacts2;

use App\Controller\Utils\Application;
use App\Entity\Modules\Contacts2\MyContactType;
use Exception;
use Ramsey\Uuid\Codec\GuidStringCodec;
use Ramsey\Uuid\Converter\Number\DegradedNumberConverter;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This form is called as subform in the "createContactCard" widget for contacts
 * It does not have submit on purpose
 * Class MyContactTypeDtoType
 * @package App\Form\Modules\Contacts2
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

        $builder
            ->add(self::KEY_NAME, null, [
                'label' => $this->app->translator->translate('forms.MyContactTypeDtoType.labels.' . self::KEY_NAME)
            ])
            ->add(self::KEY_TYPE,EntityType::class, [
                'class'         => MyContactType::class,
                'choices'       => $this->app->repositories->myContactTypeRepository->getAllNotDeleted(),
                'choice_label'  => function (MyContactType $contact_type) {
                    return $contact_type->getName();
                },
                'label' => $this->app->translator->translate('forms.MyContactTypeDtoType.labels.' . self::KEY_TYPE)
            ])
        ;
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
