<?php


namespace App\Response\Base;

use App\Listeners\Response\JwtTokenResponseListener;
use App\Services\TypeProcessor\ArrayHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * BaseResponse used for each response in the calls which come from frontend
 * Each response should extend from this class as the fronted will try to build same response on its side
 */
class BaseResponse
{
    const KEY_FQN     = "fqn";
    const KEY_CODE    = "code";
    const KEY_MESSAGE = "message";
    const KEY_SUCCESS = "success";
    const KEY_TOKEN   = "token";
    const KEY_DATA    = "data";

    const MINIMAL_FIELDS_FOR_VALID_BASE_API_RESPONSE = [
        self::KEY_CODE,
        self::KEY_SUCCESS,
        self::KEY_TOKEN,
    ];

    const KEY_DATA_RELOAD_VIEW = "reloadView";
    const KEY_DATA_BASE64 = "base64";
    private const KEY_DATA_ALL_RECORDS = "allRecords";
    private const KEY_DATA_SINGLE_RECORD = 'singleRecord';
    private const KEY_DATA_IS_LOCKED = 'isLocked';

    const DEFAULT_CODE         = Response::HTTP_BAD_REQUEST;
    const DEFAULT_MESSAGE      = "Bad request";
    const MESSAGE_INVALID_JSON = "INVALID_JSON";
    const MESSAGE_OK           = "OK";
    const MESSAGE_NOT_FOUND    = "NOT_FOUND";
    const MESSAGE_UNAUTHORIZED = "UNAUTHORIZED";

    /**
     * @var int $code
     */
    private int $code = Response::HTTP_BAD_REQUEST;

    /**
     * @var string $message
     */
    private string $message = "";

    /**
     * @var bool $success
     */
    private bool $success = false;

    /**
     * @var array $invalidFields
     */
    private array $invalidFields = [];

    /**
     * @var array $data
     */
    private array $data = [];

    /**
     * @var string $token
     */
    private string $token = "";

    /**
     * ID of the entry in external tool - not used in communication with frontend
     *
     * @var int|null
     */
    private ?int $externalIdentifier = null;

    // Muting Php stan - prevent extending and changing construct when calling `new static()`
    final public function __construct(
        ?int    $code    = null,
        ?string $message = null,
        ?bool   $success = null
    ){
        if( !empty($code) ){
            $this->code = $code;
        }

        if( !empty($message) ){
            $this->message = $message;
        }

        if( !empty($success) ){
            $this->success = $success;
        }
    }

    /**
     * Will return FQN of the class used to create this json from,
     * It's used later on to build response back from the json in for example:
     * {@see JwtTokenResponseListener::onResponse()}
     *
     * @return string
     */
    public function getFqn(): string
    {
        return static::class;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return array
     */
    public function getInvalidFields(): array
    {
        return $this->invalidFields;
    }

    /**
     * @param array $invalidFields
     */
    public function setInvalidFields(array $invalidFields): void
    {
        $this->invalidFields = $invalidFields;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function addData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Will set the field of this response to success response so that classes which extend this method will have
     * the base response `set to success`
     */
    public function prefillBaseFieldsForSuccessResponse(): void
    {
        $this->setCode(Response::HTTP_OK);;
        $this->setSuccess(true);
    }

    /**
     * Will set the field of this response to bad request response so that classes which extend this method will have
     * the base response `set to bad request`
     */
    public function prefillBaseFieldsForBadRequestResponse(): void
    {
        $this->setCode(Response::HTTP_BAD_REQUEST);;
        $this->setSuccess(false);
    }

    /**
     * Will build internal server error response
     *
     * @param string|null $message
     * @return static
     */
    public static function buildInternalServerErrorResponse(?string $message = null): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setSuccess(false);

        if( !empty($message) ){
            $response->setMessage($message);
        }

        return $response;
    }

    /**
     * Will build bad request response
     *
     * @param string $message
     * @return static
     */
    public static function buildBadRequestErrorResponse(string $message = ""): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_BAD_REQUEST);
        $response->setSuccess(false);
        $response->setMessage($message);

        return $response;
    }

    /**
     * Will build response which indicates that service is currently in maintenance
     *
     * @param string $message
     * @return static
     */
    public static function buildMaintenanceResponse(string $message = ""): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_BAD_REQUEST);
        $response->setSuccess(false);
        $response->setMessage($message);

        return $response;
    }

    /**
     * Will build access denied response
     *
     * @param string $message
     * @return static
     */
    public static function buildAccessDeniedResponse(string $message = ""): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_UNAUTHORIZED);
        $response->setSuccess(false);
        $response->setMessage($message);

        return $response;
    }

    /**
     * Will build ok response
     *
     * @return static
     * @var string $message
     */
    public static function buildOkResponse(string $message = self::MESSAGE_OK): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_OK);
        $response->setSuccess(true);
        $response->setMessage($message);

        return $response;
    }

    /**
     * Will build toggle lock response
     *
     * @param bool $isLocked
     *
     * @return static
     *
     * @var string $message
     */
    public static function buildToggleLockResponse(bool $isLocked, string $message = self::MESSAGE_OK, int $code = Response::HTTP_OK): static
    {
        $response = new static();
        $response->setCode($code);
        $response->setSuccess(true);
        $response->setMessage($message);
        $response->addData(self::KEY_DATA_IS_LOCKED, $isLocked);

        return $response;
    }

    /**
     * Will build 404 response
     */
    public static function buildNotFoundResponse(): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_NOT_FOUND);
        $response->setSuccess(false);
        $response->setMessage(self::MESSAGE_NOT_FOUND);

        return $response;
    }

    /**
     * Will build invalid json response
     *
     * @return static
     */
    public static function buildInvalidJsonResponse(): static
    {
        $response = static::buildBadRequestErrorResponse();
        $response->setMessage(self::MESSAGE_INVALID_JSON);
        return $response;
    }

    /**
     * Will build unauthorized json response
     *
     * @param string $message
     * @return static
     */
    public static function buildUnauthorizedResponse(string $message = self::MESSAGE_UNAUTHORIZED): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_UNAUTHORIZED);
        $response->setSuccess(false);
        $response->setMessage($message);
        return $response;
    }

    /**
     * Will build bad request response, but let easily setting the invalid fields that can be handled on front
     *
     * @param string $message
     * @param array  $invalidFields
     * @return static
     */
    public static function buildInvalidFieldsRequestErrorResponse(array $invalidFields = [], string $message = ""): static
    {
        $response = new static();
        $response->setCode(Response::HTTP_BAD_REQUEST);
        $response->setSuccess(false);
        $response->setMessage($message);
        $response->setInvalidFields($invalidFields);

        return $response;
    }

    /**
     * @param int $responseCode
     * @return JsonResponse
     */
    public function toJsonResponse(int $responseCode = Response::HTTP_OK): JsonResponse
    {
        $serializer = new Serializer([
            new ObjectNormalizer(),
        ], [
            new JsonEncoder()
        ]);

        $json  = $serializer->serialize($this, "json", [
            AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT => 10,
        ]);

        $array = json_decode($json, true);

        return new JsonResponse($array, $responseCode);
    }

    /**
     * Will build the response from json
     *
     * @param string $json
     *
     * @return BaseResponse
     */
    public static function fromJson(string $json): BaseResponse
    {
        $dataArray = json_decode($json, true);

        $message = ArrayHandler::checkAndGetKey($dataArray, self::KEY_MESSAGE, self::DEFAULT_MESSAGE);
        $code    = ArrayHandler::checkAndGetKey($dataArray, self::KEY_CODE, self:: DEFAULT_CODE);
        $success = ArrayHandler::checkAndGetKey($dataArray, self::KEY_SUCCESS, false);
        $data    = ArrayHandler::checkAndGetKey($dataArray, self::KEY_DATA, []);

        $response = new BaseResponse();
        $response->setMessage($message);
        $response->setCode($code);
        $response->setSuccess($success);
        $response->setData($data);

        return $response;
    }

    /**
     * @param BaseResponse $baseApiResponse
     *
     * @return static
     */
    public static function buildFromBaseApiResponse(BaseResponse $baseApiResponse): static
    {
        $childResponse = new static();
        $childResponse->setMessage($baseApiResponse->getMessage());
        $childResponse->setSuccess($baseApiResponse->isSuccess());
        $childResponse->setInvalidFields($baseApiResponse->getInvalidFields());
        $childResponse->setData($baseApiResponse->getData());
        $childResponse->setCode($baseApiResponse->getCode());

        return $childResponse;
    }

    /**
     * Will set data key that should force front to reload the vue view.
     * Keep in mind, that this is not equal page reload.
     */
    public function reloadView(): void
    {
        $this->addData(self::KEY_DATA_RELOAD_VIEW, true);
    }

    /**
     * Will set base64 content by using data bag array
     *
     * @param string $base64
     */
    public function setBase64(string $base64): void
    {
        $this->addData(self::KEY_DATA_BASE64, $base64);
    }

    /**
     * Will set all records data using data bag array
     *
     * @param array $recordsData
     */
    public function setAllRecordsData(array $recordsData): void
    {
        $this->addData(self::KEY_DATA_ALL_RECORDS, $recordsData);
    }

    /**
     * Will set single record data using data bag array
     *
     * @param array $recordData
     */
    public function setSingleRecordData(array $recordData): void
    {
        $this->addData(self::KEY_DATA_SINGLE_RECORD, $recordData);
    }

    /**
     * @return int|null
     */
    public function getExternalIdentifier(): ?int
    {
        return $this->externalIdentifier;
    }

    /**
     * @param int|null $externalIdentifier
     */
    public function setExternalIdentifier(?int $externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

}