<?php

namespace App\Form\Modules\Passwords;

use App\Controller\Utils\Application;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPasswordsType extends AbstractType {

    /**
     * @var Application
     */
    private static $app;

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('login', TextType::class, [])
            ->add('password', TextType::class)
            ->add('url', TextType::class, [
                'required' => false
            ])
            ->add('description', TextType::class)
            ->add('group', EntityType::class, [
                'class' => MyPasswordsGroups::class,
                'choices' => static::$app->repositories->myPasswordsGroupsRepository->findBy(['deleted' => 0]),
                'choice_label' => function (MyPasswordsGroups $password_group) {
                    return $password_group->getName();
                }
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPasswords::class,
        ]);
    }
}
