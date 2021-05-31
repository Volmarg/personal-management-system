<?php

namespace App\Controller\Core;

use App\Form\Interfaces\ValidableFormInterface;
use App\Services\Core\Translator;
use App\Services\Session\AjaxCallsSessionService;
use App\VO\Validators\ValidationResultVO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AjaxResponse extends AbstractController {

    const KEY_CODE                  = "code";
    const KEY_MESSAGE               = "message";
    const KEY_TEMPLATE              = "template";
    const KEY_PASSWORD              = "password";
    const KEY_RELOAD_PAGE           = "reload_page";
    const KEY_RELOAD_MESSAGE        = "reload_message";
    const KEY_SUCCESS               = "success";
    const KEY_FORM_TEMPLATE         = "form_template";
    const KEY_VALIDATED_FORM_PREFIX = "validated_form_prefix";
    const KEY_INVALID_FORM_FIELDS   = "invalid_form_fields";
    const KEY_ROUTE_URL             = "route_url";
    const KEY_CONSTANT_VALUE        = "constant_value";
    const KEY_DATA_BAG              = "data_bag";
    const KEY_PAGE_TITLE            = "page_title";

    const XML_HTTP_HEADER_KEY   = "X-Requested-With";
    const XML_HTTP_HEADER_VALUE = "XMLHttpRequest";

    /**
     * @var int $code
     */
    private $code = Response::HTTP_OK;
    /**
     * @var string $message
     */
    private $message = "";
    /**
     * @var null|string $template
     */
    private $template = null;
    /**
     * @var null|string $password
     */
    private $password = null;
    /**
     * @var bool $reloadPage
     */
    private $reloadPage = false;
    /**
     * @var string $reloadMessage
     */
    private $reloadMessage = "";
    /**
     * @var bool $success
     */
    private $success = true;

    /**
     * Used on front to find the form fields
     * @var string $validatedFormPrefix
     */
    private $validatedFormPrefix = "";

    /**
     * @var array $invalidFormFields
     */
    private $invalidFormFields = [];

    /**
     * @var string $formTemplate
     */
    private $formTemplate = "";

    /**
     * @var string $routeUrl
     */
    private $routeUrl = "";

    /**
     * @var string $constantValue
     */
    private $constantValue = "";

    /**
     * @var array $dataBag
     */
    private $dataBag = [];

    /**
     * @var string $pageTitle
     */
    private string $pageTitle = "";

    /**
     * @return int
     */
    public function getCode(): int {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void {
        $this->message = $message;
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string {
        return $this->template;
    }

    /**
     * @param string|null $template
     */
    public function setTemplate(?string $template): void {
        $this->template = $template;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void {
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function isReloadPage(): bool {
        return $this->reloadPage;
    }

    /**
     * @param bool $reloadPage
     */
    public function setReloadPage(bool $reloadPage): void {
        $this->reloadPage = $reloadPage;
    }

    /**
     * @return string
     */
    public function getReloadMessage(): string {
        return $this->reloadMessage;
    }

    /**
     * @param string $reloadMessage
     */
    public function setReloadMessage(string $reloadMessage): void {
        $this->reloadMessage = $reloadMessage;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getFormTemplate(): string {
        return $this->formTemplate;
    }

    /**
     * @param string $formTemplate
     */
    public function setFormTemplate(string $formTemplate): void {
        $this->formTemplate = $formTemplate;
    }

    /**
     * @return string
     */
    public function getValidatedFormPrefix(): string {
        return $this->validatedFormPrefix;
    }

    /**
     * @param string $validatedFormPrefix
     */
    public function setValidatedFormPrefix(string $validatedFormPrefix): void {
        $this->validatedFormPrefix = $validatedFormPrefix;
    }

    /**
     * @return array
     */
    public function getInvalidFormFields(): array {
        return $this->invalidFormFields;
    }

    /**
     * @param array $invalidFormFields
     */
    public function setInvalidFormFields(array $invalidFormFields): void {
        $this->invalidFormFields = $invalidFormFields;
    }

    /**
     * @return string
     */
    public function getRouteUrl(): string {
        return $this->routeUrl;
    }

    /**
     * @param string $routeUrl
     */
    public function setRouteUrl(string $routeUrl): void {
        $this->routeUrl = $routeUrl;
    }

    /**
     * @return string
     */
    public function getConstantValue(): string {
        return $this->constantValue;
    }

    /**
     * @param string $constantValue
     */
    public function setConstantValue(string $constantValue): void {
        $this->constantValue = $constantValue;
    }

    /**
     * @return array
     */
    public function getDataBag(): array
    {
        return $this->dataBag;
    }

    /**
     * @param array $dataBag
     */
    public function setDataBag(array $dataBag): void
    {
        $this->dataBag = $dataBag;
    }

    /**
     * @return string
     */
    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    /**
     * @param string $pageTitle
     */
    public function setPageTitle(string $pageTitle): void
    {
        $this->pageTitle = $pageTitle;
    }

    public function __construct(string  $message = "", string $template = "")
    {
        $this->message  = $message;
        $this->template = $template;
    }

    /**
     * @param int $code
     * @param string $message
     * @param string|null $template
     * @param string|null $password
     * @param bool|null $reloadPage
     * @param string $reloadMessage
     * @param bool $success
     * @param string $formTemplate
     * @param string $validatedFormPrefix
     * @param array $invalidFormFields
     * @param array $dataBag
     * @return JsonResponse
     * @throws Exception
     */
    public static function buildJsonResponseForAjaxCall(
        int     $code,
        string  $message             = "",
        ?string $template            = null,
        ?string $password            = null,
        ?bool   $reloadPage          = false,
        string  $reloadMessage       = "",
        bool    $success             = true,
        string  $formTemplate        = "",
        string  $validatedFormPrefix = "",
        array   $invalidFormFields   = [],
        array   $dataBag             = [],
        string  $pageTitle           = ""
    ): JsonResponse {

        $responseData = [
            self::KEY_CODE    => $code,
            self::KEY_MESSAGE => $message,
        ];

        if( !empty($template) ){
            $responseData[self::KEY_TEMPLATE] = $template;
        }

        if( !empty($password) ){
            $responseData[self::KEY_PASSWORD] = $password;
        }

        if( !$reloadPage ){
            $reloadPage = self::getPageReloadStateFromSession();
        }

        if( $reloadPage ){
            $reloadMessage = self::getPageReloadMessageFromSession();
        }

        $responseData[self::KEY_RELOAD_PAGE]           = $reloadPage;
        $responseData[self::KEY_RELOAD_MESSAGE]        = $reloadMessage;
        $responseData[self::KEY_SUCCESS]               = $success;
        $responseData[self::KEY_FORM_TEMPLATE]         = $formTemplate;
        $responseData[self::KEY_VALIDATED_FORM_PREFIX] = $validatedFormPrefix;
        $responseData[self::KEY_INVALID_FORM_FIELDS]   = $invalidFormFields;
        $responseData[self::KEY_DATA_BAG]              = $dataBag;
        $responseData[self::KEY_PAGE_TITLE]            = $pageTitle;

        $response = new JsonResponse($responseData, 200);
        return $response;
    }

    /**
     * Will pre-fill code/message from Response and return AjaxResponse
     * @param Response $response
     * @return AjaxResponse
     */
    public static function initializeFromResponse(Response $response): AjaxResponse
    {
        $ajaxResponseDto = new AjaxResponse();

        $message = $response->getContent();
        $code    = $response->getStatusCode();

        $ajaxResponseDto->setMessage($message);
        $ajaxResponseDto->setCode($code);

        return $ajaxResponseDto;
    }
    
    /**
     * Transforms AjaxResponse to JsonResponse usable for Ajax call
     * @return JsonResponse
     */
    public function buildJsonResponse(): JsonResponse
    {
        $responseData = [
            self::KEY_CODE                  => $this->getCode(),
            self::KEY_MESSAGE               => $this->getMessage(),
            self::KEY_TEMPLATE              => $this->getTemplate(),
            self::KEY_PASSWORD              => $this->getPassword(),
            self::KEY_RELOAD_PAGE           => $this->isReloadPage(),
            self::KEY_RELOAD_MESSAGE        => $this->getReloadMessage(),
            self::KEY_SUCCESS               => $this->isSuccess(),
            self::KEY_FORM_TEMPLATE         => $this->getFormTemplate(),
            self::KEY_VALIDATED_FORM_PREFIX => $this->getValidatedFormPrefix(),
            self::KEY_INVALID_FORM_FIELDS   => $this->getInvalidFormFields(),
            self::KEY_ROUTE_URL             => $this->getRouteUrl(),
            self::KEY_CONSTANT_VALUE        => $this->getConstantValue(),
            self::KEY_DATA_BAG              => $this->getDataBag(),
            self::KEY_PAGE_TITLE            => $this->getPageTitle(),
        ];

        $response = new JsonResponse($responseData, 200);
        return $response;
    }

    /**
     * Will build the ajax response for invalid validation result
     * At this moment this works only with form being provided here, this was added to dynamically obtain
     * form prefix - the same prefix is automatically added by symfony in twig, so by sending the prefix with
     * ajax response it's possible to automatically attach invalid fields to the form
     *
     * @param ValidationResultVO    $validationResult
     * @param FormInterface         $usedForm
     * @param Translator            $translator
     * @param string                $reloadedTemplateContent
     * @return AjaxResponse
     * @throws Exception
     */
    public static function buildAjaxResponseForValidationResult(ValidationResultVO $validationResult, FormInterface $usedForm, Translator $translator, string $reloadedTemplateContent = ""): AjaxResponse
    {
        $usedFormType = $usedForm->getConfig()->getType()->getInnerType();

        if( $validationResult->isValid() ){
            throw new Exception("The validation result is valid! Add check in caller before calling this method!");
        }

        if( !($usedFormType instanceof ValidableFormInterface) ){
            throw new Exception("Provided form does not implement: " . ValidableFormInterface::class);
        }

        $message      = $translator->translate('messages.general.couldNotHandleTheRequest');
        $ajaxResponse = new AjaxResponse();

        $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
        $ajaxResponse->setSuccess(false);
        $ajaxResponse->setMessage($message);
        $ajaxResponse->setTemplate($reloadedTemplateContent);
        $ajaxResponse->setInvalidFormFields($validationResult->getInvalidFieldsMessages());
        $ajaxResponse->setValidatedFormPrefix($usedFormType::getFormPrefix());

        return $ajaxResponse;
    }

    /**
     * Checking if maybe somewhere in whole lifetime some event, kernel etc. emitted this data to session,
     *  as it's impossible to pass such data directly to controller/service from some places of the project
     * @return bool
     * @throws Exception
     */
    private static function getPageReloadStateFromSession(): bool
    {
        if( !AjaxCallsSessionService::hasPageReloadAfterAjaxCall() ){
            return false;
        }

        $reloadPage = AjaxCallsSessionService::getPageReloadAfterAjaxCall();
        return $reloadPage;
    }

    /**
     * Checking if maybe somewhere in whole lifetime some event, kernel etc. emitted this data to session,
     *  as it's impossible to pass such data directly to controller/service from some places of the project
     * @return string
     * @throws Exception
     */
    private static function getPageReloadMessageFromSession():string
    {
        if( !AjaxCallsSessionService::hasPageReloadMessageAfterAjaxCall() ){
            return "";
        }

        $message = AjaxCallsSessionService::getPageReloadMessageAfterAjaxCall();
        return $message;
    }
}
