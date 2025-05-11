<?php

namespace App\Action\Core;

use App\Action\Files\FileUploadAction;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Modules\ModulesController;
use App\Controller\Utils\Utils;
use App\Entity\System\LockedResource;
use App\Form\Modules\Contacts\MyContactTypeDtoType;
use App\Form\Modules\Issues\MyIssueContactType;
use App\Form\Modules\Issues\MyIssueProgressType;
use App\Form\Modules\Todo\MyTodoType;
use App\Form\System\SystemLockResourcesPasswordType;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This class is only responsible for building dialogs data in response for example on Ajax call
 * Class Dialogs
 * @package App\Controller\Utils
 */
class DialogsAction extends AbstractController
{

}
