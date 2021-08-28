<?php

namespace App\Services\Security;

use App\Services\Session\SessionsService;
use Exception;
use Psr\Log\LoggerInterface;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EncryptionService
{

    /**
     * @var EncryptorInterface $encryptor
     */
    private EncryptorInterface $encryptor;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * @var string $encryptionKey
     */
    private string $encryptionKey;

    /**
     * @var ParameterBagInterface $parameterBag
     */
    private ParameterBagInterface $parameterBag;

    /**
     * @var SessionsService $sessionsService
     */
    private SessionsService $sessionsService;

    public function __construct(EncryptorInterface $encryptor, LoggerInterface $logger, ParameterBagInterface $parameterBag, SessionsService $sessionsService)
    {
        $this->logger          = $logger;
        $this->encryptor       = $encryptor;
        $this->parameterBag    = $parameterBag;
        $this->sessionsService = $sessionsService;
    }

    /**
     * Will initialize properties / data for service
     * @param string|null $encryptionKey
     * @throws Exception
     */
    public function initialize(?string $encryptionKey = null)
    {
        $this->setEncryptionKey($encryptionKey);

        if( empty($this->encryptionKey) ){
            throw new Exception("Encryption key is not set");
        }
    }

    /**
     * Will encrypt the file
     *
     * @param string $filePath
     * @return bool
     */
    public function encryptFile(string $filePath): bool
    {
        try{

            if( !file_exists($filePath) ){
                $message = "File does not exist: " . $filePath;
                $this->logger->warning($message);
                throw new Exception($message);
            }

            $fileContent          = file_get_contents($filePath);
            $encryptedFileContent = $this->encryptor->encrypt($fileContent);
            file_put_contents($filePath, $encryptedFileContent);

        }catch(Exception | \TypeError $e){
            $this->logger->critical("Exception was thrown", [
                "message" => $e->getMessage(),
                "trace"   => $e->getTraceAsString(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Will decrypt the file
     *
     * @param string $encryptedFilePath
     * @return bool
     */
    public function decryptFile(string $encryptedFilePath): bool
    {
        try{

            if( !file_exists($encryptedFilePath) ){
                $message = "Encrypted file does not exist: " . $encryptedFilePath;
                $this->logger->warning($message);
                throw new Exception($message);
            }

            $encryptedFileContent = file_get_contents($encryptedFilePath);
            $decryptedFileContent = $this->encryptor->decrypt($encryptedFileContent);
            file_put_contents($encryptedFilePath, $decryptedFileContent);

        }catch(Exception | \TypeError $e){
            $this->logger->critical("Exception was thrown", [
                "message" => $e->getMessage(),
                "trace"   => $e->getTraceAsString(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Will decrypt the file
     *
     * @param string $encryptedFilePath
     * @return string
     */
    public function decryptFileContent(string $encryptedFilePath): string
    {
        try{

            if( !file_exists($encryptedFilePath) ){
                $message = "Encrypted file does not exist: " . $encryptedFilePath;
                $this->logger->warning($message);
                throw new Exception($message);
            }

            $encryptedFileContent = file_get_contents($encryptedFilePath);
            $decryptedFileContent = $this->encryptor->decrypt($encryptedFileContent);

            return $decryptedFileContent;

        }catch(Exception | \TypeError $e){
            $this->logger->critical("Exception was thrown", [
                "message" => $e->getMessage(),
                "trace"   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Will set encryption based on settings / provided data, decides how key will be obtained:
     * - from session (if provided via form etc.)
     * - from yaml (if hardcoded in configuration files)
     * - directly provided as string,
     *
     * @param string|null $encryptionKey
     */
    public function setEncryptionKey(?string $encryptionKey = null): void
    {
        $this->encryptionKey = $encryptionKey;
        if( is_null($encryptionKey) ){

            if( $this->parameterBag->has('encrypt_key') ){

                $this->encryptionKey = $this->parameterBag->get('encrypt_key');
            }elseif( $this->sessionsService->hasEncryptionKey() ){

                $this->encryptionKey = $this->sessionsService->getEncryptionKey();
            }

        }
    }
}