<?php

namespace App\Form\User;

use App\Controller\Core\Application;
use App\Controller\Utils\Utils;
use App\DTO\User\UserRegistrationDTO;
use App\Entity\User;
use App\Form\Interfaces\ValidableFormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegisterType extends AbstractType implements ValidableFormInterface
{
    const FIELD_USERNAME             = "username";
    const FIELD_EMAIL                = "email";
    const FIELD_PASSWORD             = "password";
    const FIELD_PASSWORD_REPEAT      = "passwordRepeat";
    const FIELD_LOCK_PASSWORD        = "lockPassword";
    const FIELD_LOCK_PASSWORD_REPEAT = "lockPasswordRepeat";
    const FIELD_SUBMIT               = "submit";

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @return string
     */
    public static function getFormPrefix(): string {
        return Utils::getClassBasename(User::class);
    }

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(self::FIELD_USERNAME,TextType::class,[
                'label'   => $this->app->translator->translate('forms.UserRegisterType.'.self::FIELD_USERNAME)
            ])
            ->add(self::FIELD_EMAIL, TextType::class, [
                'label'   => $this->app->translator->translate('forms.UserRegisterType.'.self::FIELD_EMAIL)
            ])
            ->add(self::FIELD_PASSWORD, PasswordType::class,[
                'label'   => $this->app->translator->translate('forms.UserRegisterType.'.self::FIELD_PASSWORD)
            ])
            ->add(self::FIELD_PASSWORD_REPEAT, PasswordType::class,[
                'label'   => $this->app->translator->translate('forms.UserRegisterType.'.self::FIELD_PASSWORD_REPEAT)
            ])
            ->add(self::FIELD_LOCK_PASSWORD, PasswordType::class,[
                'label'   => $this->app->translator->translate('forms.UserRegisterType.'.self::FIELD_LOCK_PASSWORD)
            ])
            ->add(self::FIELD_LOCK_PASSWORD_REPEAT, PasswordType::class,[
                'label'   => $this->app->translator->translate('forms.UserRegisterType.'.self::FIELD_LOCK_PASSWORD_REPEAT)
            ])
            ->add(self::FIELD_SUBMIT, SubmitType::class, [
                'label'   => $this->app->translator->translate('forms.UserRegisterType.'.self::FIELD_SUBMIT)
            ])
        ;
    }

    // add some logic for auto filling other elements of entity - canonical
    // add some logic to hash password
    // add some logic to check if passwords are the same
    // add new form type to see password on eye click
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserRegistrationDTO::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string {
        return self::getFormPrefix();
    }
}
