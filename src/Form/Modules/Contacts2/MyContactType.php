<?php

namespace App\Form\Modules\Contacts2;

use App\Controller\Utils\Application;
use App\Entity\Modules\Contacts\MyContactsGroups;
use App\Entity\Modules\Contacts2\MyContact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Modules\Contacts2\MyContactType as MyContactTypeEntity;

class MyContactType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        switch ($options['type']) {
            case 'phone':
                $label = $this->app->translator->translate('forms.MyContactsType.phone');
                break;
            case 'email':
                $label = $this->app->translator->translate('forms.MyContactsType.email');
                break;
            case 'other':
                $label = $this->app->translator->translate('forms.MyContactsType.other');
                break;
            case 'archived':
                $label = $this->app->translator->translate('forms.MyContactsType.archived');
                break;
            default:
                throw new \Exception('Incorrect type was provided');
        }


        $builder
            ->add('contact', TextType::class, [
                'label' => $label
            ])
            ->add('type', HiddenType::class, [
                'data'  => $options['type'],
                'label' => $this->app->translator->translate('forms.MyContactsType.type')
            ])
            ->add('description',TextType::class, [
                'label' => $this->app->translator->translate('forms.MyContactsType.description')
            ])
            ->add('group', EntityType::class, [
                'class'         => MyContactTypeEntity::class,
                'choices'       => $this->app->repositories->myContactTypeRepository->findBy(['deleted' => 0]),
                'choice_label'  => function (MyContactsGroups $contact_group) {
                    return $contact_group->getName();
                },
                'label' => $this->app->translator->translate('forms.MyContactsType.group')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyContact::class,
        ]);
        $resolver->setRequired('type');
    }
}
