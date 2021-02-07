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
     * @var bool $reload_page
     */
    private $reload_page = false;
    /**
     * @var string $reload_message
     */
    private $reload_message = "";
    /**
     * @var bool $success
     */
    private $success = true;

    /**
     * Used on front to find the form fields
     * @var string $validated_form_prefix
     */
    private $validated_form_prefix = "";

    /**
     * @var array $invalid_form_fields
     */
    private $invalid_form_fields = [];

    /**
     * @var string $form_template
     */
    private $form_template = "";

    /**
     * @var string $route_url
     */
    private $route_url = "";

    /**
     * @var string $constant_value
     */
    private $constant_value = "";

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
        return $this->reload_page;
    }

    /**
     * @param bool $reload_page
     */
    public function setReloadPage(bool $reload_page): void {
        $this->reload_page = $reload_page;
    }

    /**
     * @return string
     */
    public function getReloadMessage(): string {
        return $this->reload_message;
    }

    /**
     * @param string $reload_message
     */
    public function setReloadMessage(string $reload_message): void {
        $this->reload_message = $reload_message;
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
        return $this->form_template;
    }

    /**
     * @param string $form_template
     */
    public function setFormTemplate(string $form_template): void {
        $this->form_template = $form_template;
    }

    /**
     * @return string
     */
    public function getValidatedFormPrefix(): string {
        return $this->validated_form_prefix;
    }

    /**
     * @param string $validated_form_prefix
     */
    public function setValidatedFormPrefix(string $validated_form_prefix): void {
        $this->validated_form_prefix = $validated_form_prefix;
    }

    /**
     * @return array
     */
    public function getInvalidFormFields(): array {
        return $this->invalid_form_fields;
    }

    /**
     * @param array $invalid_form_fields
     */
    public function setInvalidFormFields(array $invalid_form_fields): void {
        $this->invalid_form_fields = $invalid_form_fields;
    }

    /**
     * @return string
     */
    public function getRouteUrl(): string {
        return $this->route_url;
    }

    /**
     * @param string $route_url
     */
    public function setRouteUrl(string $route_url): void {
        $this->route_url = $route_url;
    }

    /**
     * @return string
     */
    public function getConstantValue(): string {
        return $this->constant_value;
    }

    /**
     * @param string $constant_value
     */
    public function setConstantValue(string $constant_value): void {
        $this->constant_value = $constant_value;
    }

    /**
     * @param int $code
     * @param string $message
     * @param string|null $template
     * @param string|null $password
     * @param bool|null $reload_page
     * @param string $reload_message
     * @param bool $success
     * @param string $form_template
     * @param string $validated_form_prefix
     * @param array $invalid_form_fields
     * @return JsonResponse
     * @throws Exception
     */
    public static function buildJsonResponseForAjaxCall(
        int     $code,
        string  $message               = "",
        ?string $template              = null,
        ?string $password              = null,
        ?bool   $reload_page           = false,
        string  $reload_message        = "",
        bool    $success               = true,
        string  $form_template         = "",
        string  $validated_form_prefix = "",
        array   $invalid_form_fields   = []
    ): JsonResponse {

        $response_data = [
            self::KEY_CODE    => $code,
            self::KEY_MESSAGE => $message,
        ];

        if( !empty($template) ){
            $response_data[self::KEY_TEMPLATE] = $template;
        }

        if( !empty($password) ){
            $response_data[self::KEY_PASSWORD] = $password;
        }

        if( !$reload_page ){
            $reload_page = self::getPageReloadStateFromSession();
        }

        if( $reload_page ){
            $reload_message = self::getPageReloadMessageFromSession();
        }

        $response_data[self::KEY_RELOAD_PAGE]           = $reload_page;
        $response_data[self::KEY_RELOAD_MESSAGE]        = $reload_message;
        $response_data[self::KEY_SUCCESS]               = $success;
        $response_data[self::KEY_FORM_TEMPLATE]         = $form_template;
        $response_data[self::KEY_VALIDATED_FORM_PREFIX] = $validated_form_prefix;
        $response_data[self::KEY_INVALID_FORM_FIELDS]   = $invalid_form_fields;

        $response = new JsonResponse($response_data, 200);
        return $response;
    }

    /**
     * Will pre-fill code/message from Response and return AjaxResponse
     * @param Response $response
     * @return AjaxResponse
     */
    public static function initializeFromResponse(Response $response): AjaxResponse
    {
        $ajax_response_dto = new AjaxResponse();

        $message = $response->getContent();
        $code    = $response->getStatusCode();

        $ajax_response_dto->setMessage($message);
        $ajax_response_dto->setCode($code);

        return $ajax_response_dto;
    }
    
    /**
     * Transforms AjaxResponse to JsonResponse usable for Ajax call
     * @return JsonResponse
     */
    public function buildJsonResponse(): JsonResponse
    {
        $code                  = $this->getCode();
        $message               = $this->getMessage();
        $template              = $this->getTemplate();
        $password              = $this->getPassword();
        $reload_page           = $this->isReloadPage();
        $reload_message        = $this->getReloadMessage();
        $success               = $this->isSuccess();
        $form_template         = $this->getFormTemplate();
        $validated_form_prefix = $this->getValidatedFormPrefix();
        $invalid_form_fields   = $this->getInvalidFormFields();
        $route_url             = $this->getRouteUrl();
        $constant_value        = $this->getConstantValue();

        $response_data = [
            self::KEY_CODE                  => $code,
            self::KEY_MESSAGE               => $message,
            self::KEY_TEMPLATE              => $template,
            self::KEY_PASSWORD              => $password,
            self::KEY_RELOAD_PAGE           => $reload_page,
            self::KEY_RELOAD_MESSAGE        => $reload_message,
            self::KEY_SUCCESS               => $success,
            self::KEY_FORM_TEMPLATE         => $form_template,
            self::KEY_VALIDATED_FORM_PREFIX => $validated_form_prefix,
            self::KEY_INVALID_FORM_FIELDS   => $invalid_form_fields,
            self::KEY_ROUTE_URL             => $route_url,
            self::KEY_CONSTANT_VALUE        => $constant_value,
        ];

        $response = new JsonResponse($response_data, 200);
        return $response;
    }

    /**
     * Will build the ajax response for invalid validation result
     *
     * @param ValidationResultVO    $validation_result
     * @param FormInterface         $used_form
     * @param Translator            $translator
     * @param string                $reloaded_template_content
     * @return AjaxResponse
     * @throws Exception
     */
    public static function buildAjaxResponseForValidationResult(ValidationResultVO $validation_result, FormInterface $used_form, Translator $translator, string $reloaded_template_content = ""): AjaxResponse
    {
        $used_form_type = $used_form->getConfig()->getType()->getInnerType();

        if( $validation_result->isValid() ){
            throw new Exception("The validation result is valid! Add check in caller before calling this method!");
        }

        if( !($used_form_type instanceof ValidableFormInterface) ){
            throw new Exception("Provided form does not implement: " . ValidableFormInterface::class);
        }

        $message       = $translator->translate('messages.general.couldNotHandleTheRequest');
        $ajax_response = new AjaxResponse();

        $ajax_response->setCode(Response::HTTP_BAD_REQUEST);
        $ajax_response->setSuccess(false);
        $ajax_response->setMessage($message);
        $ajax_response->setTemplate($reloaded_template_content);
        $ajax_response->setInvalidFormFields($validation_result->getInvalidFieldsMessages());
        $ajax_response->setValidatedFormPrefix($used_form_type::getFormPrefix());

        return $ajax_response;
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

        $reload_page = AjaxCallsSessionService::getPageReloadAfterAjaxCall();
        return $reload_page;
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
