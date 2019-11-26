<?php

namespace App\Form\Modules\Payments;

use App\Controller\Utils\Application;
use App\Entity\Modules\Payments\MyPaymentsOwed;
use App\Form\Type\RoundcheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsOwedType extends AbstractType
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('target', TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.target')
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.target')
            ])
            ->add('information', TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.information')
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.information')
            ])
            ->add('date', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off',
                    'placeholder'               => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.date')
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'required'  => false,
                'label' => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.date')
            ])
            ->add('amount', NumberType::class, [
                'attr' => [
                    'min'           => 0.1,
                    'placeholder'   => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.amount')
                ],
                'label'     => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.amount'),
            ])
            ->add('owedByMe', RoundcheckboxType::class, [
                'required' => false,
                'label' => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.owedByMe')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyPaymentsOwed::class,
        ]);
    }
}
