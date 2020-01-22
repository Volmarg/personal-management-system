<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:05
 */

namespace App\Controller\Utils;

use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Contacts\MyContactGroup;
use App\Form\Files\MoveSingleFileType;
use App\Form\Files\UpdateTagsType;
use App\Form\Files\UploadSubdirectoryCopyDataType;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Form\Files\UploadSubdirectoryRenameType;
use App\Form\Modules\Achievements\AchievementType;
use App\Form\Modules\Contacts\MyContactGroupType;
use App\Form\Modules\Contacts\MyContactType;
use App\Form\Modules\Contacts\MyContactTypeType;
use App\Form\Modules\Goals\MyGoalsPaymentsType;
use App\Form\Modules\Goals\MyGoalsType;
use App\Form\Modules\Goals\MySubgoalsType;
use App\Form\Modules\Job\MyJobHolidaysPoolType;
use App\Form\Modules\Job\MyJobHolidaysType;
use App\Form\Modules\Notes\MyNotesCategoriesType;
use App\Form\Modules\Notes\MyNotesType;
use App\Form\Modules\Passwords\MyPasswordsGroupsType;
use App\Form\Modules\Passwords\MyPasswordsType;
use App\Form\Modules\Payments\CurrencyType;
use App\Form\Modules\Payments\MyPaymentsBills;
use App\Form\Modules\Payments\MyPaymentsBillsItems;
use App\Form\Modules\Payments\MyPaymentsOwedType;
use App\Form\Modules\Payments\MyRecurringPaymentsMonthlyType;
use App\Form\Modules\Schedules\MyScheduleType;
use App\Form\Modules\Schedules\MyScheduleTypeType;
use App\Form\Modules\Shopping\MyShoppingPlansType;
use App\Form\Modules\Travels\MyTravelsIdeasType;
use App\Form\User\UserAvatarType;
use App\Form\User\UserNicknameType;
use App\Form\User\UserPasswordType;
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

    public function moveSingleFileForm(array $params = []): FormInterface {
        return $this->createForm(MoveSingleFileType::class, null, $params);
    }

    public function jobHolidaysForm(array $params = []): FormInterface {
        return $this->createForm(MyJobHolidaysType::class, null, $params);
    }

    public function jobHolidaysPoolForm(array $params = []): FormInterface {
        return $this->createForm(MyJobHolidaysPoolType::class, null, $params);
    }

    public function moneyOwedForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsOwedType::class, null, $params);
    }

    public function updateTagsForm(array $params = []): FormInterface {
        return $this->createForm(UpdateTagsType::class, null, $params);
    }

    public function uploadCreateSubdirectoryForm(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryCreateType::class, null, $params);
    }

    public function noteTypeForm(array $params = []): FormInterface {
        return $this->createForm(MyNotesType::class, null, $params);
    }

    public function paymentsBillsForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsBills::class, null, $params);
    }

    public function paymentsBillsItemsForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsBillsItems::class, null, $params);
    }

    public function recurringPaymentsForm(array $params = []): FormInterface {
        return $this->createForm(MyRecurringPaymentsMonthlyType::class, null, $params);
    }

    public function scheduleForm(array $params = []): FormInterface {
        return $this->createForm(MyScheduleType::class, null, $params);
    }

    public function scheduleTypeForm(array $params = []): FormInterface {
        return $this->createForm(MyScheduleTypeType::class, null, $params);
    }

    public function contactForm(array $params = [], MyContact $my_contact_type = null): FormInterface {
        return $this->createForm(MyContactType::class, $my_contact_type, $params);
    }

    public function contactTypeForm(array $params = []): FormInterface {
        return $this->createForm(MyContactTypeType::class, null, $params);
    }

    public function contactGroupForm(array $params = []): FormInterface {
        return $this->createForm(MyContactGroupType::class, null, $params);
    }

    public function renameSubdirectoryForm(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryRenameType::class, null, $params);
    }

    public function copyUploadSubdirectoryDataForm(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryCopyDataType::class, null, $params);
    }

    public function createSubdirectoryForm(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryCreateType::class, null, $params);
    }

    public function achievementForm(array $params = []): FormInterface {
        return $this->createForm(AchievementType::class, null, $params);
    }

    public function goalForm(array $params = []): FormInterface {
        return $this->createForm(MyGoalsType::class, null, $params);
    }

    public function subgoalForm(array $params = []): FormInterface {
        return $this->createForm(MySubgoalsType::class, null, $params);
    }

    public function goalPaymentForm(array $params = []): FormInterface {
        return $this->createForm(MyGoalsPaymentsType::class, null, $params);
    }

    public function noteCategoryForm(array $params = []): FormInterface {
        return $this->createForm(MyNotesCategoriesType::class, null, $params);
    }

    public function myPasswordForm(array $params = []): FormInterface {
        return $this->createForm(MyPasswordsType::class, null, $params);
    }

    public function passwordGroupForm(array $params = []): FormInterface {
        return $this->createForm(MyPasswordsGroupsType::class, null, $params);
    }

    public function myShoppingPlanForm(array $params = []): FormInterface {
        return $this->createForm(MyShoppingPlansType::class, null, $params);
    }

    public function travelIdeasForm(array $params = []): FormInterface {
        return $this->createForm(MyTravelsIdeasType::class, null, $params);
    }

    public function userAvatarForm(array $params = []): FormInterface {
        return $this->createForm(UserAvatarType::class, null, $params);
    }

    public function userPasswordForm(array $params = []): FormInterface {
        return $this->createForm(UserPasswordType::class, null, $params);
    }

    public function userNicknameForm(array $params = []): FormInterface {
        return $this->createForm(UserNicknameType::class, null, $params);
    }

    public function currencyTypeForm(array $params = []): FormInterface {
        return $this->createForm(CurrencyType::class, null, $params);
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

    /**
     * @param string $form_namespace
     * @param array $options
     * @return string
     */
    public function getFormViewWithoutFormTags(string $form_namespace, array $options = []): string {

        $form        = $this->createForm($form_namespace, null, $options);
        $form_view   = $form->createView();

        if( !empty($form_data) ){
            $form->setData($form_data);
        }

        $form_render = $this->render(self::TWIG_RENDERED_FORM_TEMPLATE, ['form' => $form_view])->getContent();
        //todo: better regex
        $form_content = preg_replace('#<(.*)form(.*)>#U','', $form_render,1);
        $form_content = str_replace('</form>','', $form_content);

        return $form_content;

    }
}