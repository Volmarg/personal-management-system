<?php
/**
 * @description this file handles getting content of the encrypted file (after decrypting it)
 *              this has to be handled as standalone file to avoid page performance impact
 *              when calling for ~100+ files data via src/href.
 *
 *              With symfony route each request has to go through whole Symfony lifecycle / logic and
 *              gets very slow with lots of files per page
 */

include_once "../../../../vendor/autoload.php";

use App\Controller\Core\Env;
use App\Services\Files\Parser\YamlFileParserService;
use App\Services\Security\AuthLessSecurity;
use App\Services\Security\EncryptionService;
use App\Services\Session\SessionsService;
use App\Twig\System\Security;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SpecShaper\EncryptBundle\Encryptors\OpenSslEncryptor;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

$request   = Request::createFromGlobals();
$sessionId = $request->get(Security::QUERY_PARAM_SESSION_ID);
if( !AuthLessSecurity::isLoggedIn($sessionId) ){
    header("Location: /login");
    exit();
}

const RELATIVE_UPLOAD_FOLDER_PATH = "/../../..";
const RELATIVE_ROOT_FOLDER_PATH   = "../../../../";

$env = new Dotenv();
$env->load(RELATIVE_ROOT_FOLDER_PATH . ".env");

$currentEnv = Env::getEnvironment();

$monologHandler = new StreamHandler(RELATIVE_ROOT_FOLDER_PATH . "var/log/{$currentEnv}.log");
$logger         = new Logger('app');
$logger->pushHandler($monologHandler);

try{
    $session               = new Session();
    $parameterBag          = new ParameterBag();
    $sessionService        = new SessionsService($session, null);
    $encryptionFileContent = YamlFileParserService::getFileContentAsArray(RELATIVE_ROOT_FOLDER_PATH . "config/packages/config/encryption.yaml");
    $encryptionKey         = $encryptionFileContent['parameters']['encrypt_key'];

    $encryptor         = new OpenSslEncryptor($encryptionKey);
    $encryptionService = new EncryptionService($encryptor, $logger, $parameterBag, $sessionService);

    echo getEncryptedFileContent($encryptionService, $encryptionKey, $request);
}catch(Exception | TypeError $e){
    $logger->critical("Exception was throw while trying to obtain encrypted file content.", [
        "message" => $e->getMessage(),
        "trace"   => $e->getTraceAsString(),
    ]);
    return "";
}

/**
 * Will return content of the file after decrypting it
 *
 * @param EncryptionService $encryptionService
 * @param string $encryptionKey
 * @param Request $request
 * @return string
 */
function getEncryptedFileContent(EncryptionService $encryptionService, string $encryptionKey, Request $request): string
{
    $encryptionService->setEncryptionKey($encryptionKey);

    $filePath         = urldecode($request->get(Security::QUERY_PARAM_FILE_PATH));
    $absoluteFilePath = getcwd() . RELATIVE_UPLOAD_FOLDER_PATH . $filePath;

    return $encryptionService->decryptFileContent($absoluteFilePath);
}
