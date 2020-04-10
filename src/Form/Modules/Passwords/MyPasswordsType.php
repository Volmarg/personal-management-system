<?php

namespace App\Form\Modules\Passwords;

use App\Controller\Core\Application;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPasswordsType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('login', TextType::class, [
                'label' => $this->app->translator->translate('forms.MyPasswordsType.labels.login')
            ])
            ->add('password', TextType::class, [
                'label' => $this->app->translator->translate('forms.MyPasswordsType.labels.password')

            ])
            ->add('url', TextType::class, [
                'required' => false,
                'label' => $this->app->translator->translate('forms.MyPasswordsType.labels.url')
            ])
            ->add('description', TextType::class, [
                'label' => $this->app->translator->translate('forms.MyPasswordsType.labels.description')
            ])
            ->add('group', EntityType::class, [
                'class' => MyPasswordsGroups::class,
                'choices' => $this->app->repositories->myPasswordsGroupsRepository->findBy(['deleted' => 0]),
                'choice_label' => function (MyPasswordsGroups $password_group) {
                    return $password_group->getName();
                },
                'label' => $this->app->translator->translate('forms.MyPasswordsType.labels.group')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPasswords::class,
        ]);
    }
}
