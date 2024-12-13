<?php

namespace App\Action\Modules\System\Settings;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Page\SettingsFinancesController;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;
use App\Services\TypeProcessor\ArrayHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/system/settings/finances/currencies", name: "module.system.settings.finances.currencies.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_SYSTEM])]
class FinancesCurrenciesAction extends AbstractController {

    public function __construct(
        private readonly SettingsSaver              $settingsSaverService,
        private readonly SettingsLoader             $settingsLoaderService,
        private readonly TranslatorInterface        $trans,
        private readonly SettingsFinancesController $financesController
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("", name: "new", methods: [Request::METHOD_POST])]
    public function new(Request $request): JsonResponse
    {
        return $this->createOrUpdate($request)->toJsonResponse();
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $currencies = $this->settingsLoaderService->getCurrenciesDtosForSettingsFinances();
        $entriesData = [];
        foreach ($currencies as $currency) {
            $entriesData[] = [
                'name'       => $currency->getName(),
                'multiplier' => $currency->getMultiplier(),
                'symbol'     => $currency->getSymbol(),
                'isDefault'  => $currency->isDefault(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(Request $request): JsonResponse
    {
        return $this->createOrUpdate($request)->toJsonResponse();
    }

    /**
     * This is based on ugly legacy code - cleaned a lill bit
     *
     * @param string $name
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{name}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(string $name): JsonResponse
    {
        $currencies = $this->settingsLoaderService->getCurrenciesDtosForSettingsFinances();
        $name       = trim($name);

        foreach ($currencies as $index => $currency) {
            if ($currency->getName() === $name) {
                if ($currency->isDefault()) {
                    $message = $this->trans->trans("module.system.settings.finances.currencies.msg.noRemovingDefault");
                    return BaseResponse::buildBadRequestErrorResponse($message)->toJsonResponse();
                }

                unset($currencies[$index]);
                break;
            }
        }

        $this->settingsSaverService->saveFinancesSettingsForCurrenciesSettings($currencies);

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request): BaseResponse
    {
        $dataArray  = RequestService::tryFromJsonBody($request);
        $isDefault  = ArrayHandler::get($dataArray, 'isDefault');
        $multiplier = ArrayHandler::get($dataArray, 'multiplier');
        $name       = ArrayHandler::get($dataArray, 'name');
        $oldName    = ArrayHandler::get($dataArray, 'oldName');
        $symbol     = ArrayHandler::get($dataArray, 'symbol');

        $requestDto = new SettingsCurrencyDTO();
        $requestDto->setName($name);
        $requestDto->setSymbol($symbol);
        $requestDto->setMultiplier($multiplier);
        $requestDto->setIsDefault($isDefault);

        $dbDtos = $this->settingsLoaderService->getCurrenciesDtosForSettingsFinances();
        if (!empty($oldName)) {
            return $this->updateExisting($dbDtos, $oldName, $requestDto);
        }

        $matches = array_filter($dbDtos, fn(SettingsCurrencyDTO $setting) => $setting->getName() == $requestDto->getName());
        if (!empty($matches)) {
            $msg = $this->trans->trans('module.system.settings.finances.currencies.msg.nameAlreadyInUse');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        return $this->addNew($isDefault, $dbDtos, $requestDto);
    }

    /**
     * This is based on ugly legacy code - cleaned a lill bit
     *
     * @param array               $dbDtos
     * @param string              $oldName
     * @param SettingsCurrencyDTO $requestDto
     *
     * @return BaseResponse
     * @throws Exception
     */
    public function updateExisting(array $dbDtos, string $oldName, SettingsCurrencyDTO $requestDto): BaseResponse
    {
        $unsetIndex = null;
        foreach ($dbDtos as $index => $dbSingleSetting) {
            if ($dbSingleSetting->getName() === $oldName) {
                $unsetIndex = $index;
            }

            if ($dbSingleSetting->getName() === $requestDto->getName() && $oldName !== $requestDto->getName()) {
                $msg = $this->trans->trans('module.system.settings.finances.currencies.msg.nameAlreadyInUse');
                return BaseResponse::buildBadRequestErrorResponse($msg);
            }
        }

        if ($requestDto->isDefault()) {
            $dbDtos = $this->financesController->handleDefaultCurrencyChange($dbDtos, $requestDto, $unsetIndex);
        }

        $dbDtos   = $this->financesController->handleCurrencyUpdate($dbDtos, $requestDto, $unsetIndex);
        $defaults = array_filter($dbDtos, fn(SettingsCurrencyDTO $setting) => $setting->isDefault());
        if (!$defaults) {
            $msg = $this->trans->trans('module.system.settings.finances.currencies.msg.noDefaultCurrency');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        $this->settingsSaverService->saveFinancesSettingsForCurrenciesSettings($dbDtos);

        return BaseResponse::buildOkResponse();
    }

    /**
     * @param mixed               $isDefault
     * @param array               $dbDtos
     * @param SettingsCurrencyDTO $requestDto
     *
     * @return BaseResponse
     * @throws Exception
     */
    public function addNew(mixed $isDefault, array $dbDtos, SettingsCurrencyDTO $requestDto): BaseResponse
    {
        // handle checking if default value already exist and if so then unset all and set new
        if ($isDefault) {
            foreach ($dbDtos as $singleSettingDto) {
                $singleSettingDto->setIsDefault(false);
            }

            $dbDtos[] = $requestDto;
            $this->settingsSaverService->saveFinancesSettingsForCurrenciesSettings($dbDtos);
        } else {
            // just add new record
            $validationResult = $this->financesController->addCurrencyToFinancesCurrencySettings($requestDto);
            if (!$validationResult->isValid()) {
                return BaseResponse::buildBadRequestErrorResponse($validationResult->getMessage());
            }
        }

        return BaseResponse::buildOkResponse();
    }

}