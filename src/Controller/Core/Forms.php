<?php
namespace App\Controller\Core;

use App\Action\Core\FormsAction;
use App\Entity\Modules\Contacts\MyContact;
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
use App\Form\Modules\Issues\MyIssueContactType;
use App\Form\Modules\Issues\MyIssueProgressType;
use App\Form\Modules\Job\MyJobHolidaysPoolType;
use App\Form\Modules\Job\MyJobHolidaysType;
use App\Form\Modules\Notes\MyNotesCategoriesType;
use App\Form\Modules\Notes\MyNotesType;
use App\Form\Modules\Passwords\MyPasswordsGroupsType;
use App\Form\Modules\Passwords\MyPasswordsType;
use App\Form\Modules\Payments\MyPaymentsBills;
use App\Form\Modules\Payments\MyPaymentsBillsItems;
use App\Form\Modules\Payments\MyPaymentsIncomeType;
use App\Form\Modules\Payments\MyPaymentsOwedType;
use App\Form\Modules\Payments\MyPaymentsProductsType;
use App\Form\Modules\Payments\MyPaymentsSettingsCurrencyMultiplierType;
use App\Form\Modules\Payments\MyPaymentsTypesType;
use App\Form\Modules\Payments\MyRecurringPaymentsMonthlyType;
use App\Form\Modules\Issues\MyIssueType;
use App\Form\Modules\Schedules\MyScheduleType;
use App\Form\Modules\Schedules\MyScheduleTypeType;
use App\Form\Modules\Shopping\MyShoppingPlansType;
use App\Form\Modules\Todo\MyTodoElementType;
use App\Form\Modules\Todo\MyTodoType;
use App\Form\Modules\Travels\MyTravelsIdeasType;
use App\Form\Page\Settings\Finances\CurrencyType;
use App\Form\System\SystemLockResourcesPasswordType;
use App\Form\UploadFormType;
use App\Form\User\UserAvatarType;
use App\Form\User\UserNicknameType;
use App\Form\User\UserPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class Forms extends AbstractController {

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

    public function moneyIncomeForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsIncomeType::class, null, $params);
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

    public function systemLockResourcesPasswordForm(array $params = []): FormInterface {
        return $this->createForm(SystemLockResourcesPasswordType::class, null, $params);
    }

    public function paymentsProductsForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsProductsType::class, null, $params);
    }

    public function paymentsTypesForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsTypesType::class, null, $params);
    }

    public function currencyMultiplierForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsSettingsCurrencyMultiplierType::class, null, $params);
    }

    public function uploadForm(array $params = []): FormInterface {
        return $this->createForm(UploadFormType::class, null, $params);
    }

    public function issueForm(array $params = []): FormInterface {
        return $this->createForm(MyIssueType::class, null, $params);
    }

    public function issueProgressForm(array $params = []): FormInterface {
        return $this->createForm(MyIssueProgressType::class, null, $params);
    }

    public function issueContactForm(array $params = []): FormInterface {
        return $this->createForm(MyIssueContactType::class, null, $params);
    }

    public function todoForm(array $params = []): FormInterface {
        return $this->createForm(MyTodoType::class, null, $params);
    }

    public function todoElementForm(array $params = []): FormInterface {
        return $this->createForm(MyTodoElementType::class, null, $params);
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

        $form_render = $this->render(FormsAction::TWIG_RENDERED_FORM_TEMPLATE, ['form' => $form_view])->getContent();
        //todo: better regex
        $form_content = preg_replace('#<(.*)form(.*)>#U','', $form_render,1);
        $form_content = str_replace('</form>','', $form_content);

        return $form_content;

    }
}