<?php

namespace App\Twig\System;

use App\Controller\System\SecurityController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Security extends AbstractExtension {

    const QUERY_PARAM_FILE_PATH  = "filePath";
    const QUERY_PARAM_SESSION_ID = "sessionId";

    /**
     * @var SecurityController $securityController
     */
    private SecurityController $securityController;

    public function __construct(SecurityController $securityController) {
        $this->securityController = $securityController;
    }

    public function getFunctions() {
        return [
            new TwigFunction('canRegisterUser', [$this, 'canRegisterUser']),
            new TwigFunction('getSessionId', [$this, 'getSessionId']),
            new TwigFunction('generateUrlForGettingDecryptedFileContent', [$this, 'generateUrlForGettingDecryptedFileContent']),
        ];
    }

    /**
     * Returns the information if it's allowed to register user in system
     *
     * @return bool
     */
    public function canRegisterUser(): bool
    {
        return $this->securityController->canRegisterUser();
    }

    /**
     * Returns the current session id
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return session_id();
    }

    /**
     * Will generate url used for getting decrypted file content
     *
     * @param string $filePath
     * @param string $sessionId
     * @return string
     */
    public function generateUrlForGettingDecryptedFileContent(string $filePath, string $sessionId): string
    {
        $url = "/action/system/encryption/getEncryptedFileContent.php";

        $queryString = http_build_query([
            self::QUERY_PARAM_FILE_PATH  => $filePath,
            self::QUERY_PARAM_SESSION_ID => $sessionId,
        ]);

        $urlWithParams = $url . "?" . $queryString;
        return $urlWithParams;
    }

}