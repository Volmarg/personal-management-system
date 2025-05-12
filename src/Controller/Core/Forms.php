<?php
namespace App\Controller\Core;

use App\Form\Files\UploadSubdirectoryCopyDataType;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Form\Files\UploadSubdirectoryRenameType;
use App\Form\Files\ModuleAndDirectorySelectType;
use App\Form\Modules\Goals\MyGoalsPaymentsType;
use App\Form\Modules\Payments\MyPaymentsSettingsCurrencyMultiplierType;
use App\Form\Page\Settings\Finances\CurrencyType;
use App\Form\UploadFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class Forms extends AbstractController {

    public function renameSubdirectoryForm(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryRenameType::class, null, $params);
    }

    public function copyUploadSubdirectoryDataForm(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryCopyDataType::class, null, $params);
    }

    public function createSubdirectoryForm(array $params = []): FormInterface {
        return $this->createForm(UploadSubdirectoryCreateType::class, null, $params);
    }

    public function goalPaymentForm(array $params = []): FormInterface {
        return $this->createForm(MyGoalsPaymentsType::class, null, $params);
    }

    public function currencyTypeForm(array $params = []): FormInterface {
        return $this->createForm(CurrencyType::class, null, $params);
    }

    public function currencyMultiplierForm(array $params = []): FormInterface {
        return $this->createForm(MyPaymentsSettingsCurrencyMultiplierType::class, null, $params);
    }

    public function uploadForm(array $params = []): FormInterface {
        return $this->createForm(UploadFormType::class, null, $params);
    }

    public function getModuleAndDirectorySelectForm(array $params = []): FormInterface{
        return $this->createForm(ModuleAndDirectorySelectType::class, null, $params);
    }

}