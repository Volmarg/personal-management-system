<?php

namespace App\Services\External;

use App\Controller\Core\Application;
use App\PmsIo\DTO\Notes\NoteCategoryDTO;
use App\PmsIo\DTO\Notes\NoteDTO;
use App\PmsIo\DTO\Passwords\PasswordDTO;
use App\PmsIo\DTO\Passwords\PasswordGroupDTO;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use App\PmsIo\PmsIoBridge;
use App\PmsIo\Request\Notes\InsertNotesCategoriesRequest;
use App\PmsIo\Request\Notes\InsertNotesRequest;
use App\PmsIo\Request\Passwords\InsertPasswordsGroupsRequest;
use App\PmsIo\Request\Passwords\InsertPasswordsRequest;
use App\PmsIo\Request\System\IsAllowedToInsertRequest;
use App\PmsIo\Request\System\SetTransferDoneStateRequest;
use App\PmsIo\Response\Notes\InsertNotesCategoriesResponse;
use App\PmsIo\Response\Notes\InsertNotesResponse;
use App\PmsIo\Response\Passwords\InsertPasswordsGroupsResponse;
use App\PmsIo\Response\Passwords\InsertPasswordsResponse;
use App\PmsIo\Response\System\IsAllowedToInsertResponse;
use App\PmsIo\Response\System\SetTransferDoneStateResponse;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;

/**
 * Handles communication with PMS-IO
 *
 * Class PmsIoService
 * @package App\Services\External
 */
class PmsIoService
{

    /**
     * @var PmsIoBridge $pmsIoBridge
     */
    private PmsIoBridge $pmsIoBridge;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var EncryptorInterface $encryptor
     */
    private EncryptorInterface $encryptor;

    /**
     * @param PmsIoBridge $pmsIoBridge
     * @param Application $app
     * @param EncryptorInterface $encryptor
     */
    public function __construct(PmsIoBridge $pmsIoBridge, Application $app, EncryptorInterface $encryptor)
    {
        $this->app         = $app;
        $this->encryptor   = $encryptor;
        $this->pmsIoBridge = $pmsIoBridge;
    }

    /**
     * Will insert passwords to the PMS-IO
     * - the password is already encrypted in this project
     * - encrypts fields which are decrypted on PMS-IO side
     *
     * @param MyPasswords[] $passwords
     * @return InsertPasswordsResponse
     * @throws Exception
     * @throws GuzzleException
     */
    public function insertPasswords(array $passwords): InsertPasswordsResponse
    {
        try{
            $passwordsToInsert      = [];
            $insertPasswordsRequest = new InsertPasswordsRequest();
            foreach($passwords as $password){
                $encryptedLogin       = $this->encryptor->encrypt($password->getLogin());
                $encryptedUrl         = $this->encryptor->encrypt($password->getUrl()) ?? "";
                $encryptedDescription = $this->encryptor->encrypt($password->getDescription() ?? "");
                $encryptedPassword    = $this->encryptor->encrypt($password->getPassword());

                $passwordDto = new PasswordDTO();
                $passwordDto->setDescription($encryptedDescription);
                $passwordDto->setGroupId($password->getGroup()->getId());
                $passwordDto->setId($password->getId());
                $passwordDto->setPassword($encryptedPassword);
                $passwordDto->setLogin($encryptedLogin);
                $passwordDto->setUrl($encryptedUrl);

                $passwordsToInsert[] = $passwordDto;
            }

            $insertPasswordsRequest->setPasswordDtos($passwordsToInsert);
            $response = $this->pmsIoBridge->insertPasswords($insertPasswordsRequest);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }

    /**
     * Will insert passwords groups to the PMS-IO
     * - encrypts fields which are decrypted on PMS-IO side
     *
     * @param MyPasswordsGroups[] $passwordsGroups
     * @return InsertPasswordsGroupsResponse
     * @throws Exception
     * @throws GuzzleException
     */
    public function insertPasswordsGroups(array $passwordsGroups): InsertPasswordsGroupsResponse
    {
        try{
            $passwordsGroupsToInsert      = [];
            $insertPasswordsGroupsRequest = new InsertPasswordsGroupsRequest();
            foreach($passwordsGroups as $passwordGroup){
                $encryptedName = $this->encryptor->encrypt($passwordGroup->getName());

                $passwordGroupDto = new PasswordGroupDTO();
                $passwordGroupDto->setId($passwordGroup->getId());
                $passwordGroupDto->setName($encryptedName);

                $passwordsGroupsToInsert[] = $passwordGroupDto;
            }

            $insertPasswordsGroupsRequest->setPasswordsGroupsDtos($passwordsGroupsToInsert);
            $response = $this->pmsIoBridge->insertPasswordsGroups($insertPasswordsGroupsRequest);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }

    /**
     * Will insert notes to the PMS-IO
     * - encrypts fields which are decrypted on PMS-IO side
     *
     * @param MyNotes[] $notes
     * @return InsertNotesResponse
     * @throws Exception
     * @throws GuzzleException
     */
    public function insertNotes(array $notes): InsertNotesResponse
    {
        try{
            $notesToInsert      = [];
            $insertNotesRequest = new InsertNotesRequest();
            foreach($notes as $note){
                $encryptedTitle = $this->encryptor->encrypt($note->getTitle());
                $encryptedBody  = $this->encryptor->encrypt($note->getBody() ?? "");

                $noteDto = new NoteDTO();
                $noteDto->setId($note->getId());
                $noteDto->setCategoryId($note->getCategory()->getId());
                $noteDto->setTitle($encryptedTitle);
                $noteDto->setBody($encryptedBody);

                $notesToInsert[] = $noteDto;
            }

            $insertNotesRequest->setNotesDtos($notesToInsert);
            $response = $this->pmsIoBridge->insertNotes($insertNotesRequest);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }

    /**
     * Will insert notes categories to the PMS-IO
     * - encrypts fields which are decrypted on PMS-IO side
     *
     * @param MyNotesCategories[] $notesCategories
     * @return InsertNotesCategoriesResponse
     * @throws Exception
     * @throws GuzzleException
     */
    public function insertNotesCategories(array $notesCategories): InsertNotesCategoriesResponse
    {
        try{
            $notesCategoriesToInsert = [];
            $insertNotesRequest      = new InsertNotesCategoriesRequest();
            foreach($notesCategories as $noteCategory){
                $encryptedName = $this->encryptor->encrypt($noteCategory->getName());

                $noteCategoryDto = new NoteCategoryDTO();
                $noteCategoryDto->setId($noteCategory->getId());
                $noteCategoryDto->setParentId($noteCategory->getParentId() ?? "");
                $noteCategoryDto->setColor($noteCategory->getColor() ?? "");
                $noteCategoryDto->setIcon($noteCategory->getIcon() ?? "");
                $noteCategoryDto->setName($encryptedName);

                $notesCategoriesToInsert[] = $noteCategoryDto;
            }

            $insertNotesRequest->setCategoriesDtos($notesCategoriesToInsert);
            $response = $this->pmsIoBridge->insertNotesCategories($insertNotesRequest);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }

    /**
     * Will set the transfer status to done in PMS-IO
     *
     * @return SetTransferDoneStateResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function setTransferDoneState(): SetTransferDoneStateResponse
    {
        try{
            $request = new SetTransferDoneStateRequest();
            $response = $this->pmsIoBridge->setTransferDoneState($request);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }

    /**
     * Will check if any data can be inserted at all
     *
     * @return IsAllowedToInsertResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function isAllowedToInsert(): IsAllowedToInsertResponse
    {
        try{
            $request  = new IsAllowedToInsertRequest();
            $response = $this->pmsIoBridge->isAllowedToInsert($request);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }

}