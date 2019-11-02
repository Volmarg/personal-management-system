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
use App\Form\Modules\Job\MyJobHolidaysPoolType;
use App\Form\Modules\Job\MyJobHolidaysType;
use App\Form\Modules\Notes\MyNotesType;
use App\Form\Modules\Payments\MyPaymentsBills;
use App\Form\Modules\Payments\MyPaymentsBillsItems;
use App\Form\Modules\Payments\MyPaymentsOwedType;
use App\Form\Modules\Payments\MyRecurringPaymentsMonthlyType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class Forms extends AbstractController {

    public function __construct() {

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

}