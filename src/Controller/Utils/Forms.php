<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:05
 */

namespace App\Controller\Utils;

use App\Form\Files\MoveSingleFileType;
use App\Form\Files\UpdateTagsType;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Form\Modules\Contacts2\MyContactType;
use App\Form\Modules\Job\MyJobHolidaysPoolType;
use App\Form\Modules\Job\MyJobHolidaysType;
use App\Form\Modules\Notes\MyNotesType;
use App\Form\Modules\Payments\MyPaymentsBills;
use App\Form\Modules\Payments\MyPaymentsBillsItems;
use App\Form\Modules\Payments\MyPaymentsOwedType;
use App\Form\Modules\Payments\MyRecurringPaymentsMonthlyType;
use App\Form\Modules\Schedules\MyScheduleType;
use App\Form\Modules\Schedules\MyScheduleTypeType;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Forms extends AbstractController {

    const TWIG_RENDERED_FORM_TEMPLATE = 'page-elements/components/forms/rendered-form.twig';

    const KEY_FORM_NAMESPACE = 'form_namespace';

    /**
     * @var Translator $translator
     */
    private $translator;

    public function __construct(Translator $translator) {
        $this->translator = $translator;
    }

    public function moveSingleFile(array $params = []): FormInterface {
        return $this->createForm(MoveSingleFileType::class, null, $params);
    }

    public function jobHolidays(array $params = []): FormInterface {
        return $this->createForm(MyJobHolidaysType::class, null, $params);
    }

    public function jobHolidaysPool(array $params = []): FormInterface {
        return $this->createForm(MyJobHolidaysPoolType::class, null, $params);
    }

    public function moneyOwed(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsOwedType::class, null, $params);
    }

    public function updateTags(array $params = []): FormInterface {
        return $this->createForm(UpdateTagsType::class, null, $params);
    }

    public function uploadCreateSubdirectory(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryCreateType::class, null, $params);
    }

    public function createNote(array $params = []): FormInterface {
        return $this->createForm(MyNotesType::class, null, $params);
    }

    public function paymentsBills(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsBills::class, null, $params);
    }

    public function paymentsBillsItems(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsBillsItems::class, null, $params);
    }

    public function recurringPayments(array $params = []): FormInterface {
        return $this->createForm(MyRecurringPaymentsMonthlyType::class, null, $params);
    }

    public function schedule(array $params = []): FormInterface {
        return $this->createForm(MyScheduleType::class, null, $params);
    }

    public function scheduleType(array $params = []): FormInterface {
        return $this->createForm(MyScheduleTypeType::class, null, $params);
    }

    public function contact(array $params = []): FormInterface {
        return $this->createForm(MyContactType::class, null, $params);
    }

    /**
     * This function is used on frontend to fetch the form
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionDuplicatedTranslationKey
     * @Route("/api/get-form-view-by-class-name", name="get_form_view_by_class_name", methods="POST")
     */
    public function getFormViewByClassName(Request $request):JsonResponse {

        if( !$request->request->has(self::KEY_FORM_NAMESPACE) ){
            $message = $this->translator->translate('responses.general.missingRequiredParameter') . self::KEY_FORM_NAMESPACE;

            $data = [
                'error' => $message,
            ];
            return new JsonResponse($data);
        }

        $form_namespace = $request->request->get(self::KEY_FORM_NAMESPACE);

        try{
            $form       = $this->createForm($form_namespace)->createView();
            $form_view  = $this->render(self::TWIG_RENDERED_FORM_TEMPLATE, ['form' => $form] )->getContent();

            $data = [
                'form_view' => $form_view,
            ];

        }catch(\Exception $e){
            $message = $this->translator->translate('forms.general.error.couldNotLoadFormForGivenNamespace');

            $data = [
                'error' => $message,
            ];
        }

        return new JsonResponse($data);
    }
}