<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:02
 */

namespace App\Controller\Core;


use App\Controller\Utils\Utils;
use App\Services\Validation\EntityValidatorService;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Contacts\MyContactGroup;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use App\Entity\Modules\Todo\MyTodo;
use App\Repository\FilesSearchRepository;
use App\Repository\FilesTagsRepository;
use App\Repository\Modules\Achievements\AchievementRepository;
use App\Repository\Modules\Contacts\MyContactGroupRepository;
use App\Repository\Modules\Contacts\MyContactRepository;
use App\Repository\Modules\Contacts\MyContactTypeRepository;
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Repository\Modules\Issues\MyIssueContactRepository;
use App\Repository\Modules\Issues\MyIssueProgressRepository;
use App\Repository\Modules\Issues\MyIssueRepository;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use App\Repository\Modules\Job\MyJobHolidaysPoolRepository;
use App\Repository\Modules\Job\MyJobHolidaysRepository;
use App\Repository\Modules\Job\MyJobSettingsRepository;
use App\Repository\Modules\ModuleDataRepository;
use App\Repository\Modules\Notes\MyNotesRepository;
use App\Repository\Modules\Notes\MyNotesCategoriesRepository;
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
use App\Repository\Modules\Payments\MyPaymentsBillsItemsRepository;
use App\Repository\Modules\Payments\MyPaymentsBillsRepository;
use App\Repository\Modules\Payments\MyPaymentsIncomeRepository;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use App\Repository\Modules\Payments\MyPaymentsOwedRepository;
use App\Repository\Modules\Payments\MyPaymentsProductRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\Modules\Payments\MyRecurringPaymentMonthlyRepository;
use App\Repository\Modules\Reports\ReportsRepository;
use App\Repository\Modules\Schedules\MyScheduleCalendarRepository;
use App\Repository\Modules\Schedules\MyScheduleReminderRepository;
use App\Repository\Modules\Schedules\MyScheduleRepository;
use App\Repository\Modules\Shopping\MyShoppingPlansRepository;
use App\Repository\Modules\Todo\MyTodoElementRepository;
use App\Repository\Modules\Todo\MyTodoRepository;
use App\Repository\Modules\Travels\MyTravelsIdeasRepository;
use App\Repository\SettingRepository;
use App\Repository\System\LockedResourceRepository;
use App\Repository\System\ModuleRepository;
use App\Repository\UserRepository;
use App\Services\Exceptions\ExceptionRepository;
use App\Services\Core\Translator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TypeError;

class Repositories extends AbstractController {

    const ACHIEVEMENT_REPOSITORY_NAME                   = 'AchievementRepository';
    const MY_NOTES_REPOSITORY_NAME                      = 'MyNotesRepository';
    const MY_NOTES_CATEGORIES_REPOSITORY_NAME           = 'MyNotesCategoriesRepository';
    const MY_JOB_AFTERHOURS_REPOSITORY_NAME             = 'MyJobAfterhoursRepository';
    const MY_PAYMENTS_MONTHLY_REPOSITORY_NAME           = 'MyPaymentsMonthlyRepository';
    const MY_PAYMENTS_PRODUCTS_REPOSITORY_NAME          = 'MyPaymentsProductRepository';
    const MY_PAYMENTS_SETTINGS_REPOSITORY_NAME          = 'MyPaymentsSettingsRepository';
    const MY_SHOPPING_PLANS_REPOSITORY_NAME             = 'MyShoppingPlansRepository';
    const MY_TRAVELS_IDEAS_REPOSITORY_NAME              = 'MyTravelsIdeasRepository';
    const INTEGRATIONS_RESOURCES_REPOSITORY_NAME        = 'IntegrationResourceRepository';
    const MY_PASSWORDS_REPOSITORY_NAME                  = 'MyPasswordsRepository';
    const MY_PASSWORDS_GROUPS_REPOSITORY_NAME           = 'MyPasswordsGroupsRepository';
    const USER_REPOSITORY                               = 'UserRepository';
    const MY_GOALS_PAYMENTS_REPOSITORY_NAME             = 'MyGoalsPaymentsRepository';
    const MY_JOB_HOLIDAYS_REPOSITORY_NAME               = 'MyJobHolidaysRepository';
    const MY_JOB_HOLIDAYS_POOL_REPOSITORY_NAME          = 'MyJobHolidaysPoolRepository';
    const MY_JOB_SETTINGS_REPOSITORY_NAME               = 'MyJobSettingsRepository';
    const MY_PAYMENTS_OWED_REPOSITORY_NAME              = 'MyPaymentsOwedRepository';
    const MY_PAYMENTS_INCOME_REPOSITORY_NAME            = 'MyPaymentsIncomeRepository';
    const MY_PAYMENTS_BILLS_REPOSITORY_NAME             = 'MyPaymentsBillsRepository';
    const MY_PAYMENTS_BILLS_ITEMS_REPOSITORY_NAME       = 'MyPaymentsBillsItemsRepository';
    const FILE_TAGS_REPOSITORY                          = 'FilesTagsRepository';
    const REPORTS_REPOSITORY                            = 'ReportsRepository';
    const MY_RECURRING_PAYMENT_MONTHLY_REPOSITORY_NAME  = 'MyRecurringPaymentMonthlyRepository';
    const SETTING_REPOSITORY                            = 'SettingRepository';
    const MY_SCHEDULE_REPOSITORY                        = "MyScheduleRepository";
    const MY_SCHEDULE_CALENDAR_REPOSITORY               = "MyScheduleCalendarRepository";
    const MY_CONTACT_REPOSITORY                         = "MyContactRepository";
    const MY_CONTACT_TYPE_REPOSITORY                    = "MyContactTypeRepository";
    const MY_CONTACT_GROUP_REPOSITORY                   = "MyContactGroupRepository";
    const MY_ISSUES_REPOSITORY                          = "MyIssueRepository";
    const MY_ISSUES_CONTACT_REPOSITORY                  = "MyIssueContactRepository";
    const MY_ISSUES_PROGRESS_REPOSITORY                 = "MyIssueProgressRepository";
    const MY_TODO_REPOSITORY                            = "MyTodoRepository";
    const MY_TODO_ELEMENT_REPOSITORY                    = "MyTodoElementRepository";
    const MODULE_DATA_REPOSITORY                        = "ModuleDataRepository";
    const MY_SCHEDULE_REMINDERS_CONTROLLER              = "MyScheduleRemindersController";

    const PASSWORD_FIELD        = 'password';
    const PARENT_ID_FIELD       = 'parent_id';
    const NAME_FIELD            = 'name';
    const FIELD_TYPE_ENTITY     = 'entity';

    const KEY_MESSAGE           = "message";
    const KEY_REPOSITORY        = "repository";
    const KEY_ID                = "id";

    const KEY_SERIALIZED_FORM_DATA = "serializedFormData";

    const KEY_ENTITY_DATA_IS_NULL = "isNull";
    const KEY_ENTITY_DATA_TYPE    = "type";
    const ENTITY_DATA_TYPE_ENTITY = "entity";

    const KEY_CLASS_META_RELATED_ENTITY_FIELD_NAME          = "fieldName";
    const KEY_CLASS_META_RELATED_ENTITY_FIELD_TARGET_ENTITY = "targetEntity";
    const KEY_CLASS_META_RELATED_ENTITY_MAPPED_BY           = "mappedBy";

    const ENTITY_PROPERTY_DELETED = "deleted";
    const ENTITY_PROXY_KEY        = "Proxies";

    const ENTITY_GET_DELETED_METHOD_NAME = "getDeleted";
    const ENTITY_IS_DELETED_METHOD_NAME  = "isDeleted";

    const DOCTRINE_FIELD_MAPPING_TYPE_BOOLEAN  = "boolean";
    const DOCTRINE_FIELD_MAPPING_TYPE_DATETIME = "datetime";

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * @var MyNotesRepository $myNotesRepository
     */
    public $myNotesRepository;

    /**
     * @var AchievementRepository
     */
    public $achievementRepository;

    /**
     * @var MyJobAfterhoursRepository
     */
    public $myJobAfterhoursRepository;

    /**
     * @var MyPaymentsMonthlyRepository
     */
    public $myPaymentsMonthlyRepository;

    /**
     * @var MyPaymentsProductRepository
     */
    public $myPaymentsProductRepository;

    /**
     * @var MyShoppingPlansRepository
     */
    public $myShoppingPlansRepository;

    /**
     * @var MyTravelsIdeasRepository
     */
    public $myTravelsIdeasRepository;

    /**
     * @var MyPaymentsSettingsRepository
     */
    public $myPaymentsSettingsRepository;

    /**
     * @var MyNotesCategoriesRepository
     */
    public $myNotesCategoriesRepository;

    /**
     * @var MyPasswordsRepository
     */
    public $myPasswordsRepository;

    /**
     * @var MyPasswordsGroupsRepository
     */
    public $myPasswordsGroupsRepository;

    /**
     * @var UserRepository
     */
    public $userRepository;

    /**
     * @var MyGoalsPaymentsRepository
     */
    public $myGoalsPaymentsRepository;

    /**
     * @var MyJobHolidaysRepository
     */
    public $myJobHolidaysRepository;

    /**
     * @var MyJobHolidaysPoolRepository
     */
    public $myJobHolidaysPoolRepository;

    /**
     * @var MyJobSettingsRepository
     */
    public $myJobSettingsRepository;

    /**
     * @var MyPaymentsOwedRepository
     */
    public $myPaymentsOwedRepository;

    /**
     * @var FilesTagsRepository
     */
    public $filesTagsRepository;

    /**
     * @var FilesSearchRepository $filesSearchRepository
     */
    public $filesSearchRepository;

    /**
     * @var MyPaymentsBillsRepository $myPaymentsBillsRepository
     */
    public $myPaymentsBillsRepository;

    /**
     * @var MyPaymentsBillsItemsRepository $myPaymentsBillsItemsRepository
     */
    public $myPaymentsBillsItemsRepository;

    /**
     * @var ReportsRepository $reportsRepository
     */
    public $reportsRepository;

    /**
     * @var MyRecurringPaymentMonthlyRepository
     */
    public $myRecurringPaymentMonthlyRepository;

    /**
     * @var SettingRepository
     */
    public $settingRepository;

    /**
     * @var MyScheduleRepository $myScheduleRepository
     */
    public $myScheduleRepository;

    /**
     * @var MyContactRepository $myContactRepository
     */
    public $myContactRepository;

    /**
     * @var MyContactTypeRepository $myContactTypeRepository
     */
    public $myContactTypeRepository;

    /**
     * @var MyContactGroupRepository $myContactGroupRepository
     */
    public $myContactGroupRepository;

    /**
     * @var MyPaymentsIncomeRepository $myPaymentsIncomeRepository
     */
    public $myPaymentsIncomeRepository;

    /**
     * @var LockedResourceRepository $lockedResourceRepository
     */
    public $lockedResourceRepository;

    /**
     * @var MyIssueRepository $myIssueRepository
     */
    public $myIssueRepository;

    /**
     * @var MyIssueContactRepository $myIssueContactRepository
     */
    public $myIssueContactRepository;

    /**
     * @var MyIssueProgressRepository $myIssueProgressRepository
     */
    public $myIssueProgressRepository;

    /**
     * @var MyTodoRepository $myTodoRepository
     */
    public $myTodoRepository;

    /**
     * @var MyTodoElementRepository $myTodoElementRepository
     */
    public $myTodoElementRepository;

    /**
     * @var ModuleRepository $moduleRepository
     */
    public $moduleRepository;

    /**
     * @var MyScheduleCalendarRepository $myScheduleCalendarRepository
     */
    public $myScheduleCalendarRepository;

    /**
     * @var ModuleDataRepository $moduleDataRepository
     */
    public $moduleDataRepository;

    /**
     * @var MyScheduleReminderRepository $myScheduleReminderRepository
     */
    public MyScheduleReminderRepository $myScheduleReminderRepository;

    /**
     * @var EntityValidatorService $entityValidator
     */
    private $entityValidator;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct(
        MyNotesRepository                   $myNotesRepository,
        AchievementRepository               $myAchievementsRepository,
        MyJobAfterhoursRepository           $myJobAfterhoursRepository,
        MyPaymentsMonthlyRepository         $myPaymentsMonthlyRepository,
        MyPaymentsProductRepository         $myPaymentsProductRepository,
        MyShoppingPlansRepository           $myShoppingPlansRepository,
        MyTravelsIdeasRepository            $myTravelIdeasRepository,
        MyPaymentsSettingsRepository        $myPaymentsSettingsRepository,
        MyNotesCategoriesRepository         $myNotesCategoriesRepository,
        MyPasswordsRepository               $myPasswordsRepository,
        MyPasswordsGroupsRepository         $myPasswordsGroupsRepository,
        UserRepository                      $userRepository,
        MyGoalsPaymentsRepository           $myGoalsPaymentsRepository,
        MyJobHolidaysRepository             $myJobHolidaysRepository,
        MyJobHolidaysPoolRepository         $myJobHolidaysPoolRepository,
        MyJobSettingsRepository             $myJobSettingsRepository,
        MyPaymentsOwedRepository            $myPaymentsOwedRepository,
        FilesTagsRepository                 $filesTagsRepository,
        FilesSearchRepository               $filesSearchRepository,
        Translator                          $translator,
        MyPaymentsBillsRepository           $myPaymentsBillsRepository,
        MyPaymentsBillsItemsRepository      $myPaymentsBillsItemsRepository,
        ReportsRepository                   $reportsRepository,
        MyRecurringPaymentMonthlyRepository $myRecurringMonthlyPaymentRepository,
        SettingRepository                   $settingRepository,
        MyScheduleRepository                $myScheduleRepository,
        MyContactTypeRepository             $myContactTypeRepository,
        MyContactGroupRepository            $myContactGroupRepository,
        MyContactRepository                 $myContactRepository,
        MyPaymentsIncomeRepository          $myPaymentsIncomeRepository,
        LockedResourceRepository            $lockedResourceRepository,
        MyIssueRepository                   $myIssueRepository,
        MyIssueContactRepository            $myIssueContactRepository,
        MyIssueProgressRepository           $myIssueProgressRepository,
        MyTodoRepository                    $myTodoRepository,
        MyTodoElementRepository             $myTodoElementRepository,
        ModuleRepository                    $moduleRepository,
        ModuleDataRepository                $moduleDataRepository,
        MyScheduleCalendarRepository        $myScheduleCalendarRepository,
        MyScheduleReminderRepository        $myScheduleReminderRepository,
        EntityManagerInterface              $entityManager,
        EntityValidatorService                     $entityValidator,
        LoggerInterface                     $logger
    ) {
        $this->myNotesRepository                    = $myNotesRepository;
        $this->achievementRepository                = $myAchievementsRepository;
        $this->myJobAfterhoursRepository            = $myJobAfterhoursRepository;
        $this->myPaymentsMonthlyRepository          = $myPaymentsMonthlyRepository;
        $this->myPaymentsProductRepository          = $myPaymentsProductRepository;
        $this->myShoppingPlansRepository            = $myShoppingPlansRepository;
        $this->myTravelsIdeasRepository             = $myTravelIdeasRepository;
        $this->myPaymentsSettingsRepository         = $myPaymentsSettingsRepository;
        $this->myNotesCategoriesRepository          = $myNotesCategoriesRepository;
        $this->myPasswordsRepository                = $myPasswordsRepository;
        $this->myPasswordsGroupsRepository          = $myPasswordsGroupsRepository;
        $this->userRepository                       = $userRepository;
        $this->myGoalsPaymentsRepository            = $myGoalsPaymentsRepository;
        $this->myJobHolidaysRepository              = $myJobHolidaysRepository;
        $this->myJobHolidaysPoolRepository          = $myJobHolidaysPoolRepository;
        $this->myJobSettingsRepository              = $myJobSettingsRepository;
        $this->myPaymentsOwedRepository             = $myPaymentsOwedRepository;
        $this->filesTagsRepository                  = $filesTagsRepository;
        $this->filesSearchRepository                = $filesSearchRepository;
        $this->translator                           = $translator;
        $this->myPaymentsBillsRepository            = $myPaymentsBillsRepository;
        $this->myPaymentsBillsItemsRepository       = $myPaymentsBillsItemsRepository;
        $this->reportsRepository                    = $reportsRepository;
        $this->myRecurringPaymentMonthlyRepository  = $myRecurringMonthlyPaymentRepository;
        $this->settingRepository                    = $settingRepository;
        $this->myScheduleRepository                 = $myScheduleRepository;
        $this->myContactTypeRepository              = $myContactTypeRepository;
        $this->myContactGroupRepository             = $myContactGroupRepository;
        $this->myContactRepository                  = $myContactRepository;
        $this->myPaymentsIncomeRepository           = $myPaymentsIncomeRepository;
        $this->lockedResourceRepository             = $lockedResourceRepository;
        $this->myIssueRepository                    = $myIssueRepository;
        $this->myIssueContactRepository             = $myIssueContactRepository;
        $this->myIssueProgressRepository            = $myIssueProgressRepository;
        $this->entityManager                        = $entityManager;
        $this->entityValidator                      = $entityValidator;
        $this->myTodoRepository                     = $myTodoRepository;
        $this->myTodoElementRepository              = $myTodoElementRepository;
        $this->moduleRepository                     = $moduleRepository;
        $this->moduleDataRepository                 = $moduleDataRepository;
        $this->myScheduleCalendarRepository         = $myScheduleCalendarRepository;
        $this->myScheduleReminderRepository         = $myScheduleReminderRepository;
        $this->logger                               = $logger;
    }

    /**
     *  This is general method for all common record soft delete called from front
     *  Also request is present only when calling via ajax, that's why in some places AjaxResponse is being sent back
     * @param string $repositoryName
     * @param $id
     * @param array $findByParams
     * @param Request|null $request
     * @return Response
     *
     * @throws Exception
     */
    public function deleteById(string $repositoryName, $id, array $findByParams = [], ?Request $request = null ): Response {
        try {

            $id = $this->trimAndCheckId($id);
            $this->logger->info("Now handling removal for: ", [
                self::KEY_REPOSITORY => $repositoryName,
                self::KEY_ID         => $id,
            ]);

            /**
             * @var ServiceEntityRepository $repository
             */
            $repository = $this->{lcfirst($repositoryName)};
            $record     = $repository->find($id);

            // first attempt to remove the related entities, only then check if there is still some relation left
            $record = $this->handleRecordActiveRelatedEntities($record);
            if ( $this->hasRecordActiveRelatedEntities($record, $repository) ) {
                $message = $this->translator->translate('exceptions.repositories.recordHasChildrenCannotRemove');
                $this->logger->warning($message);

                if( !empty($request) ){
                    return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
                }

                return new Response($message, 500);
            }

            #Info: Reattach the entity - doctrine based issue
            $this->entityManager->clear();
            $record = $repository->find($id);

            if( !($record instanceof SoftDeletableEntityInterface) ){
                $message = $this->translator->translate("exceptions.general.thisEntityIsNotSoftDeletable");
                $this->logger->warning($message);

                if( !empty($request)){
                    return new Response($message, 500);
                }

                return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
            }

            $record->setDeleted(1);
            $record = $this->changeRecordDataBeforeSoftDelete($repositoryName, $record);

            $em = $this->getDoctrine()->getManager();

            $em->persist($record);
            $em->flush();

            $message = $this->translator->translate('responses.repositories.recordDeletedSuccessfully');

            if( !empty($request) ){
                return AjaxResponse::buildJsonResponseForAjaxCall(200, $message);
            }

            $this->logger->info($message);

            return new Response($message, 200);
        } catch (Exception | ExceptionRepository $er) {
            $message = $this->translator->translate('responses.repositories.couldNotDeleteRecord');
            $this->logger->warning($message, [
                self::KEY_MESSAGE => $er->getMessage(),
            ]);

            if( !empty($request) ){
                return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
            }

            return new Response($message, 500);
        }
    }

    /**
     * @param array $parameters
     * @param $entity
     * This is general method for all common record update called from front
     * @param array $findByParams
     * @return Response
     *
     * It's required that field for which You want to get entity has this example format in js ajax request:
     * 'category': {
     * "type": "entity",
     * 'namespace': 'App\\Entity\\MyNotesCategories',
     * 'id': $(noteCategoryId).val(),
     * },
     *
     * @throws MappingException
     * @throws Exception
     */
    public function update(array $parameters, $entity, array $findByParams = []): Response
    {

        try {
            unset($parameters['id']);

            $entity = $this->setEntityPropertiesFromArrayOfFieldsParameters($entity, $parameters);

            // check constraints now that the entity is updated
            $validationResult = $this->entityValidator->handleValidation($entity, EntityValidatorService::ACTION_UPDATE);

            if( !$validationResult->isValid() ){
                $failedValidationMessages = $validationResult->getAllFailedValidationMessagesAsSingleString();
                return new Response($failedValidationMessages, 500);
            }

            $em = $this->getDoctrine()->getManager();
            $entity = $this->changeRecordDataBeforeUpdate($entity);
            $em->persist($entity);
            $em->flush();

            $message = $this->translator->translate('responses.repositories.recordUpdateSuccess');
            return new Response($message, 200);
        } catch (ExceptionRepository $er) {
            $message = $this->translator->translate('responses.repositories.recordUpdateFail');
            return new Response($message, 500);
        }
    }

    /**
     * @param array $columnsNames
     */
    public static function removeHelperColumnsFromView(array &$columnsNames) {
        $columnsToRemove = ['deleted', 'delete'];

        foreach ($columnsToRemove as $columnToRemove) {
            $key = array_search($columnToRemove, $columnsNames);

            if (!is_null($key) && $key) {
                unset($columnsNames[$key]);
            }

        }
    }

    /**
     * Will attempt to create entity for given class and array of parameters
     * for each parameter - setter method is being searched for and if found sets the value of property,
     * if property does not exist - exception is thrown/catched
     * Not provided properties values (from parameters) are skipped which means that if field is required - will cause
     * Doctrine Exception
     *
     * It's required that field for which You want to get entity has this example format in js ajax request:
     * 'category': {
     * "type": "entity",
     * 'namespace': 'App\\Entity\\MyNotesCategories',
     * 'id': $(noteCategoryId).val(),
     * },
     *
     * @param string $entityClass
     * @param array $parameters
     * @return Response
     */
    public function createAndSaveEntityForEntityClassAndArrayOfParameters(string $entityClass, array $parameters): Response
    {
        /**
         * Id cannot be served as parameter because new entity is being created, therefore the auto_increment
         * must by applied by doctrine itself. Setting id is not allowed
         */
        if( key_exists("id", $parameters) ){
            unset($parameters["id"]);
        }

        try{
            $entity = new $entityClass();
            $entity = $this->setEntityPropertiesFromArrayOfFieldsParameters($entity, $parameters);

            $this->entityManager->persist($entity);;
            $this->entityManager->flush();
        }catch(Exception | TypeError $e ){
            $message = $this->translator->translate('responses.repositories.recordUpdateFail');

            $this->logger->critical($message, [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
            ]);
            return new Response($message, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response("", Response::HTTP_OK);
    }

    /**
     * This function trims id and rechecks if it's int
     * The problem is that js keeps getting all the whitespaces and new lines in to many places....
     *
     * @param $id
     * @return string
     * @throws Exception
     */
    public function trimAndCheckId($id){
        $id = (int) trim($id);

        if (!is_numeric($id)) {
            $message = $this->translator->translate('responses.repositories.inorrectId') . $id;
            throw new Exception($message);
        }

        return $id;
    }

    /**
     * @param $object
     * @return bool
     */
    public static function isEntity($object): bool
    {
        if(
                !is_object($object)
            ||  !($object instanceof EntityInterface)
        ){
            return false;
        }

        return true;
    }

    /**
     * @param string $tableName
     * @return array
     */
    public function getColumnsNamesForTableName(string $tableName): array
    {
        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();
        $columns       = $schemaManager->listTableColumns($tableName);

        $columnsNames = [];
        foreach($columns as $column){
            $columnsNames[] = $column->getName();
        }

        return $columnsNames;
    }

    /**
     * This function will retrieve all related entities to given entity (via relations in entity)
     * Info: might cause problems in case of using non standard methods name (instead of the ones generated by entity)
     * @param $entity
     * @return array
     * @throws Exception
     */
    public function getAllRelatedEntities($entity): array
    {
        $allRelatedEntities = [];

        $class    = get_class($entity);
        $isEntity = self::isEntity($entity);

        if( !$isEntity ){
            throw new Exception("Tried to get related entities on non entity class: {$class}");
        }

        $classMeta            = $this->entityManager->getClassMetadata($class);
        $associationsMappings = $classMeta->getAssociationMappings();

        foreach( $associationsMappings as $associationMapping ){
            $fieldName = $associationMapping[self::KEY_CLASS_META_RELATED_ENTITY_FIELD_NAME];

            $methodName = "get" .  ucfirst($fieldName);

            if( !method_exists($entity, $methodName) ){
                continue;
            }

            $dataForMethodName = $entity->$methodName();
            if( !method_exists($dataForMethodName, 'getValues') ){
                // this is a single entity relation with One `side` instead of `Many`
                if( self::isEntity($dataForMethodName) ){
                    $relatedEntities[] = $dataForMethodName;
                }
                continue;
            }

            $relatedEntities    = $dataForMethodName->getValues();
            $allRelatedEntities = array_merge($allRelatedEntities, $relatedEntities);
        }

        return $allRelatedEntities;
    }

    /**
     * This function validates given fields by set of rules
     * @param string $parameter
     * @param $value
     * @param null $entity
     * @return bool
     */
    private function isParameterValid(string $parameter, $value, $entity = null): bool
    {
        switch( $parameter ){
            case static::PASSWORD_FIELD:
            {
                $isNotEmpty = !empty($value);
                return $isNotEmpty;
            }
            break;

            case static::PARENT_ID_FIELD:
            {
                /**
                 * this is case where we try to move category to other parent but there is already category with this name
                 */
                if( $entity instanceof MyNotesCategories ){
                    $name = $entity->getName();

                    // Trigger name check only if category is moved to other parent
                    if( $value == $entity->getParentId() ){
                        return true;
                    }

                    $foundCorrespondingNotesCategories = $this->myNotesCategoriesRepository->getNotDeletedCategoriesForParentIdAndName($name, $value);
                    $categoryWithThisNameExistInParent = !empty($foundCorrespondingNotesCategories);

                    if ($categoryWithThisNameExistInParent) {
                        return false;
                    }
                }
            }
            break;

            case static::NAME_FIELD:
                {
                    /**
                     * this is case where we got some child but we change it's name to already existing category
                     */
                    if( $entity instanceof MyNotesCategories ){
                        $name     = $entity->getName();
                        $parentId = $entity->getParentId();

                        // Trigger name check only if name has changed, icon could change for example
                        if( $value == $name ){
                            return true;
                        }

                        $foundCorrespondingNotesCategories = $this->myNotesCategoriesRepository->getNotDeletedCategoriesForParentIdAndName($value, $parentId);
                        $categoryWithThisNameExistInParent = !empty($foundCorrespondingNotesCategories);

                        if ($categoryWithThisNameExistInParent) {
                            return false;
                        }
                    }
                }

            default:
                return true;
        }

        return true;
    }

    /**
     * @param string $parameter
     * @param $value
     * @param $entity
     * @return Response
     */
    private function decideResponseForInvalidUpdateParameter(string $parameter, $value, $entity = null): Response
    {
        $defaultMessage = $this->translator->translate('responses.general.invalidParameterValue');
        $defaultCode    = 400;

        if( $entity instanceof MyNotesCategories ){

            switch( $parameter ){
                /**
                 * this is case where parent is changed but we got such category name already in parent
                 */
                case self::PARENT_ID_FIELD:
                {
                    if( $value != $entity->getParentId() ){
                        $message = $this->translator->translate('notes.category.error.categoryWithThisNameAlreadyExistsInThisParent');
                        return new Response($message, 400);
                    }
                }
                break;

                /**
                 * this is case where name in parent changed but there is already category with this name
                 */
                case self::NAME_FIELD:
                    {
                        if( $value != $entity->getName() ){
                            $message = $this->translator->translate('notes.category.error.categoryWithThisNameAlreadyExistsInThisParent');
                            return new Response($message, 400);
                        }
                    }
                    break;

            }
        }

        return new Response($defaultMessage, $defaultCode);
    }

    /**
     * This function changes the record before soft delete
     * @param string $repositoryName
     * @param $record
     * @return MyContact
     */
    private function changeRecordDataBeforeSoftDelete(string $repositoryName, $record) {

        switch( $repositoryName ){
            case self::MY_CONTACT_REPOSITORY:
                /**
                 * @var MyContact $record
                 */
                $record->setGroup(NULL);
                break;
        }

        return $record;
    }

    /**
     * This function changes the record before update
     * @param $record
     * @return MyContact
     */
    private function changeRecordDataBeforeUpdate($record) {

        if(
                $record instanceof MyContactGroup
            ||  $record instanceof MyScheduleCalendar
        ){
            $color           = $record->getColor();
            $normalizedColor = str_replace("#", "", $color);

            $record->setColor($normalizedColor);
        }

        return $record;
    }

    /**
     * This function will handle soft removal for related entities
     * @param object $entity
     * @return array
     * @throws Exception
     */
    private function handleCascadeSoftDeleteRelatedEntities($entity)
    {
        $className = get_class($entity);

        if( $entity instanceof SoftDeletableEntityInterface ){
            $relatedEntities = $this->getAllRelatedEntities($entity);

            if( empty($relatedEntities) ){
                return $relatedEntities;
            }

            foreach($relatedEntities as $relatedEntity){
                if( $relatedEntity instanceof SoftDeletableEntityInterface ){
                    $relatedEntity->setDeleted(true);
                }
            }

        }else{
            $this->entityManager->rollback();
            throw new Exception("This entity ({$className}) does not implements soft delete interface");
        }

        return $relatedEntities;
    }


    /**
     * @param array $entityData
     * @return object|null
     */
    private function getEntity(array $entityData) {
        $entity = null;

        try {

            if (array_key_exists('namespace', $entityData) && array_key_exists('id', $entityData)) {
                $entity = $this->getDoctrine()->getRepository($entityData['namespace'])->find($entityData['id']);
            }

        } catch (ExceptionRepository $er) {
            echo $er->getMessage();
        }

        return $entity;
    }

    /**
     * @param $record
     * @param EntityRepository $repository
     * @return bool
     * This method is used to define weather the record can be soft deleted or not,
     * it has to handle all the variety of associations between records
     * because for example it should not be possible to remove category when there are some other records in it
     * @throws Exception
     */
    private function hasRecordActiveRelatedEntities($record, $repository): bool {

        # First if this is not entity for some reason then ignore it, as this applies only to entities
        $recordClassName = get_class($record);
        $classMeta       = $this->entityManager->getClassMetadata($recordClassName);
        $tableName       = $classMeta->getTableName();
        $isRecordEntity  = self::isEntity($record);

        if( !$isRecordEntity ){
            return false;
        }

        # Second thing is that some tables have relation to self without foreign key - this must be checked separately
        $parentKeys      = ['parent', 'parent_id', 'parentId', 'parentID'];
        $hasSelfRelation = false;

        foreach ($parentKeys as $key) {

            if (property_exists($record, $key)) {
                $childRecord     = $repository->findOneBy([$key => $record->getId(), self::ENTITY_PROPERTY_DELETED => 0]);
                $hasSelfRelation = true;
            }

            if (
                    isset($childRecord)
                &&  !empty($childRecord)
                &&  (
                    (
                            method_exists($childRecord, self::ENTITY_GET_DELETED_METHOD_NAME)
                        &&  !$childRecord->getDeleted()
                    )
                    ||
                    (
                            method_exists($childRecord, self::ENTITY_IS_DELETED_METHOD_NAME)
                        &&  !$childRecord->isDeleted()
                    )
                )
            ) {
                return true;
            }

        }

        # Third
        # We need to check weather we deal with parent or child
        # the child can be removed, the problem is parent as with active children he must stay
        # symfony adds _id to every foreign key so if there is property with _id we can assume that it is a children
        # info: this may cause problems if there will be advanced(i doubt there will) relations (not just parent/child)

        $columnsNames = $this->getColumnsNamesForTableName($tableName);
        foreach( $columnsNames as $columnName ){
            # we have a child so we can remove it
            if( strstr($columnName, "_id") && !$hasSelfRelation ){
                return false;
            }
        }

        # we have parent so we need to check what's the state of all of his children
        # we have to find which fields are relational ones

        $relatedEntities = $this->getAllRelatedEntities($record);
        foreach( $relatedEntities as $relatedEntity ){

            if( method_exists($relatedEntity, self::ENTITY_GET_DELETED_METHOD_NAME) ){
                $isDeleted = $relatedEntity->getDeleted();

                if(!$isDeleted){
                    return true;
                }
            }

            if( method_exists($relatedEntity, self::ENTITY_IS_DELETED_METHOD_NAME) ){
                $isDeleted = $relatedEntity->isDeleted();

                if(!$isDeleted){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param object $entity
     * @return object
     * @throws Exception
     */
    private function handleRecordActiveRelatedEntities($entity)
    {
        $this->entityManager->beginTransaction();
        {
            $relatedEntities = [];
            if( !is_object($entity) ) {
                $message = $this->translator->translate("exceptions.general.providedEntityIsNotAnObject");
                throw new Exception($message);
            }

            if( $entity instanceof  MyTodo ){
                $relatedEntities = $this->handleCascadeSoftDeleteRelatedEntities($entity);
                $myIssue         = $entity->getMyIssue();

                if( !empty($myIssue) ){
                    $myIssue->setTodo(null);
                    $this->entityManager->persist($myIssue);
                }

            }elseif( $entity instanceof MySchedule ){ // this is required to prevent removing calendar related to schedule
                $reminders = $entity->getMyScheduleReminders();

                foreach($reminders as $reminder){
                    $reminder->setDeleted(true);
                    $this->entityManager->persist($reminder);
                }
                $this->entityManager->flush();
            }elseif(
                    ($entity instanceof MyIssue)
                ||  ($entity instanceof MyNotesCategories)
                ||  ($entity instanceof MyScheduleCalendar)
            ){
                $relatedEntities = $this->handleCascadeSoftDeleteRelatedEntities($entity);
            }

            foreach($relatedEntities as $relatedEntity){
                $this->entityManager->persist($relatedEntity);
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }
        $this->entityManager->commit();

        return $entity;
    }

    /**
     * This method will set properties of entity for each parameter provided in array
     *
     * @param EntityInterface $entity
     * @param array $parameters
     * @return EntityInterface
     * @throws MappingException
     * @throws Exception
     */
    private function setEntityPropertiesFromArrayOfFieldsParameters(EntityInterface $entity, array $parameters): EntityInterface
    {
        foreach ($parameters as $parameter => $value) {

            /**
             * The only situation where this will be array is entity type parameter - does not need to be trimmed
             */
            if(!is_array($value)){
                $value = trim($value);
            }

            if ($value === "true") {
                $value = true;
            }
            if ($value === "false") {
                $value = false;
            }

            $isParameterValid = $this->isParameterValid($parameter, $value, $entity);

            if (!$isParameterValid) {
                $response = $this->decideResponseForInvalidUpdateParameter($parameter, $value, $entity);
                return $response;
            }

            // here the value of parameter sent from front can be `entity` representation object
            if (
                    is_array($value)
                &&  array_key_exists(self::KEY_ENTITY_DATA_TYPE, $value)
                &&  $value[self::KEY_ENTITY_DATA_TYPE] == self::ENTITY_DATA_TYPE_ENTITY
            ) {
                $isEntityForceNull = (
                        array_key_exists(self::KEY_ENTITY_DATA_IS_NULL, $value)
                    &&  Utils::getBoolRepresentationOfBoolString($value[self::KEY_ENTITY_DATA_IS_NULL])
                );

                if( $isEntityForceNull ){
                    $value = null;
                }else{
                    $value = $this->getEntity($value);
                }
            }
            $recordClassName  = get_class($entity);
            $classMeta        = $this->entityManager->getClassMetadata($recordClassName);

            $ucFirstParameter    = ucfirst($parameter);
            $camelCasedParameter = Application::snakeCaseToCamelCaseConverter($parameter);

            $jsonsOfAllPossiblePropertyForms = json_encode([
               $parameter, $ucFirstParameter, $camelCasedParameter
            ]);

            // this is needed to detect the type of field as doctrine sometimes want objects for it's internal mapping
            if( $classMeta->hasField($parameter) ){
                $fieldMapping = $classMeta->getFieldMapping($parameter);
                $fieldType    = $fieldMapping['type'];
                $usedProperty = $parameter;
            }elseif( $classMeta->hasField($ucFirstParameter) ){
                $fieldMapping = $classMeta->getFieldMapping($ucFirstParameter);
                $fieldType    = $fieldMapping['type'];
                $usedProperty = $ucFirstParameter;
            }elseif( $classMeta->hasField($camelCasedParameter) ){
                $fieldMapping = $classMeta->getFieldMapping($camelCasedParameter);
                $fieldType    = $fieldMapping['type'];
                $usedProperty = $camelCasedParameter;
            }elseif(
                    $classMeta->hasAssociation($parameter)
                ||  $classMeta->hasAssociation($ucFirstParameter)
                ||  $classMeta->hasAssociation($camelCasedParameter)
            ){
                $fieldType = self::FIELD_TYPE_ENTITY;
                if( property_exists($recordClassName, $parameter) ){
                    $usedProperty = $parameter;
                }elseif( property_exists($recordClassName, $ucFirstParameter) ){
                    $usedProperty = $ucFirstParameter;
                }elseif( property_exists($recordClassName, $camelCasedParameter) ){
                    $usedProperty = $camelCasedParameter;
                }else{
                    throw new Exception("Non of given properties: {$jsonsOfAllPossiblePropertyForms} exists in class: {$recordClassName}");
                }
            }else{
                throw new Exception("There is no field mapping at all for this parameter ({$parameter})?");
            }

            $methodName  = 'set' . ucfirst($usedProperty);
            $hasRelation = strstr($methodName, '_id');
            $methodName  = ( $hasRelation ? str_replace('_id', 'Id', $methodName) : $methodName);

            $value       = ( $hasRelation && empty($value) ? null : $value ); // relation field is allowed to be empty sometimes

            // we need to check some type of field in which we insert value and ew. adjust it
            switch( $fieldType ){
                case self::DOCTRINE_FIELD_MAPPING_TYPE_DATETIME:
                    {
                        $value = new \DateTime($value);
                    }
                    break;

                default:
                    // nothing
            }

            $entity->$methodName($value);
        }

        return $entity;
    }

}
