<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:02
 */

namespace App\Controller\Core;


use App\Controller\Validators\Entities\EntityValidator;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Contacts\MyContactGroup;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\Module;
use App\Repository\FilesSearchRepository;
use App\Repository\FilesTagsRepository;
use App\Repository\Modules\Achievements\AchievementRepository;
use App\Repository\Modules\Contacts\MyContactGroupRepository;
use App\Repository\Modules\Contacts\MyContactRepository;
use App\Repository\Modules\Contacts\MyContactTypeRepository;
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Repository\Modules\Goals\MyGoalsRepository;
use App\Repository\Modules\Goals\MyGoalsSubgoalsRepository;
use App\Repository\Modules\Issues\MyIssueContactRepository;
use App\Repository\Modules\Issues\MyIssueProgressRepository;
use App\Repository\Modules\Issues\MyIssueRepository;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use App\Repository\Modules\Job\MyJobHolidaysPoolRepository;
use App\Repository\Modules\Job\MyJobHolidaysRepository;
use App\Repository\Modules\Job\MyJobSettingsRepository;
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
use App\Repository\Modules\Schedules\MyScheduleRepository;
use App\Repository\Modules\Schedules\MyScheduleTypeRepository;
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
    const MY_GOALS_REPOSITORY_NAME                      = 'MyGoalsRepository';
    const MY_SUBGOALS_REPOSITORY_NAME                   = 'MyGoalsSubgoalsRepository';
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
    const MY_SCHEDULE_TYPE_REPOSITORY                   = "MyScheduleTypeRepository";
    const MY_CONTACT_REPOSITORY                         = "MyContactRepository";
    const MY_CONTACT_TYPE_REPOSITORY                    = "MyContactTypeRepository";
    const MY_CONTACT_GROUP_REPOSITORY                   = "MyContactGroupRepository";
    const LOCKED_RESOURCE_REPOSITORY                    = "LockedResourceRepository";
    const MY_ISSUES_REPOSITORY                          = "MyIssueRepository";
    const MY_ISSUES_CONTACT_REPOSITORY                  = "MyIssueContactRepository";
    const MY_ISSUES_PROGRESS_REPOSITORY                 = "MyIssueProgressRepository";
    const MY_TODO_REPOSITORY                            = "MyTodoRepository";
    const MY_TODO_ELEMENT_REPOSITORY                    = "MyTodoElementRepository";
    const MODULE_REPOSITORY                             = "ModuleRepository";

    const PASSWORD_FIELD        = 'password';
    const PARENT_ID_FIELD       = 'parent_id';
    const NAME_FIELD            = 'name';
    const FIELD_TYPE_ENTITY     = 'entity';

    const KEY_MESSAGE           = "message";
    const KEY_REPOSITORY        = "repository";
    const KEY_ID                = "id";

    const KEY_CLASS_META_RELATED_ENTITY_FIELD_NAME          = "fieldName";
    const KEY_CLASS_META_RELATED_ENTITY_FIELD_TARGET_ENTITY = "targetEntity";
    const KEY_CLASS_META_RELATED_ENTITY_MAPPED_BY           = "mappedBy";
    const KEY_CLASS_META_RELATED_ENTITY_TARGET_ENTITY       = "targetEntity";

    const ENTITY_PROPERTY_DELETED = "deleted";
    const ENTITY_PROXY_KEY        = "Proxies";

    const ENTITY_GET_DELETED_METHOD_NAME = "getDeleted";
    const ENTITY_IS_DELETED_METHOD_NAME  = "isDeleted";

    const DOCTRINE_FIELD_MAPPING_TYPE_BOOLEAN  = "boolean";
    const DOCTRINE_FIELD_MAPPING_TYPE_DATETIME = "datetime";

    /**
     * @var EntityManagerInterface $entity_manager
     */
    private $entity_manager;

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
     * @var MyGoalsRepository
     */
    public $myGoalsRepository;

    /**
     * @var MyGoalsSubgoalsRepository
     */
    public $myGoalsSubgoalsRepository;

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
     * @var MyScheduleTypeRepository $myScheduleTypeRepository
     */
    public $myScheduleTypeRepository;

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
     * @var Module $moduleRepository
     */
    public $moduleRepository;

    /**
     * @var EntityValidator $entity_validator
     */
    private $entity_validator;

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
        MyGoalsRepository                   $myGoalsRepository,
        MyGoalsSubgoalsRepository           $myGoalsSubgoalsRepository,
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
        MyScheduleTypeRepository            $myScheduleTypeRepository,
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
        EntityManagerInterface              $entity_manager,
        EntityValidator                     $entity_validator,
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
        $this->myGoalsRepository                    = $myGoalsRepository;
        $this->myGoalsSubgoalsRepository            = $myGoalsSubgoalsRepository;
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
        $this->myScheduleTypeRepository             = $myScheduleTypeRepository;
        $this->myContactTypeRepository              = $myContactTypeRepository;
        $this->myContactGroupRepository             = $myContactGroupRepository;
        $this->myContactRepository                  = $myContactRepository;
        $this->myPaymentsIncomeRepository           = $myPaymentsIncomeRepository;
        $this->lockedResourceRepository             = $lockedResourceRepository;
        $this->myIssueRepository                    = $myIssueRepository;
        $this->myIssueContactRepository             = $myIssueContactRepository;
        $this->myIssueProgressRepository            = $myIssueProgressRepository;
        $this->entity_manager                       = $entity_manager;
        $this->entity_validator                     = $entity_validator;
        $this->myTodoRepository                     = $myTodoRepository;
        $this->myTodoElementRepository              = $myTodoElementRepository;
        $this->moduleRepository                     = $moduleRepository;
        $this->logger                               = $logger;
    }

    /**
     *  This is general method for all common record soft delete called from front
     *  Also request is present only when calling via ajax, that's why in some places AjaxResponse is being sent back
     * @param string $repository_name
     * @param $id
     * @param array $findByParams
     * @param Request|null $request
     * @return Response
     *
     * @throws Exception
     */
    public function deleteById(string $repository_name, $id, array $findByParams = [], ?Request $request = null ): Response {
        try {

            $id = $this->trimAndCheckId($id);
            $this->logger->info("Now handling removal for: ", [
                self::KEY_REPOSITORY => $repository_name,
                self::KEY_ID         => $id,
            ]);

            /**
             * @var ServiceEntityRepository $repository
             */
            $repository = $this->{lcfirst($repository_name)};
            $record     = $repository->find($id);

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
            $this->entity_manager->clear();
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
            $record = $this->changeRecordDataBeforeSoftDelete($repository_name, $record);

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

                $is_parameter_valid = $this->isParameterValid($parameter, $value, $entity);

                if (!$is_parameter_valid) {
                    $response = $this->decideResponseForInvalidUpdateParameter($parameter, $value, $entity);
                    return $response;
                }

                if (is_array($value)) {
                    if (array_key_exists('type', $value) && $value['type'] == 'entity') {
                        $value = $this->getEntity($value);
                    }
                }
                $record_class_name  = get_class($entity);
                $class_meta         = $this->entity_manager->getClassMetadata($record_class_name);

                $ucFirstParameter = ucfirst($parameter);

                // this is needed to detect the type of field as doctrine sometimes want objects for it's internal mapping
                if( $class_meta->hasField($parameter) ){
                    $field_mapping = $class_meta->getFieldMapping($parameter);
                    $field_type    = $field_mapping['type'];
                }elseif( $class_meta->hasField($ucFirstParameter) ){
                    $field_mapping = $class_meta->getFieldMapping($ucFirstParameter);
                    $field_type    = $field_mapping['type'];
                }elseif( $class_meta->hasAssociation($parameter)){
                    $field_type = self::FIELD_TYPE_ENTITY;
                }elseif( $class_meta->hasAssociation($ucFirstParameter) ){
                    $field_type = self::FIELD_TYPE_ENTITY;
                }else{
                    throw new Exception("There is no field mapping at all for this parameter ({$parameter})?");
                }

                $methodName  = 'set' . $ucFirstParameter;
                $hasRelation = strstr($methodName, '_id');
                $methodName  = ( $hasRelation ? str_replace('_id', 'Id', $methodName) : $methodName);

                $value       = ( $hasRelation && empty($value) ? null : $value ); // relation field is allowed to be empty sometimes

                // we need to check some type of field in which we insert value and ew. adjust it
                switch( $field_type ){
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

            // check constraints now that the entity is updated
            $validation_result = $this->entity_validator->handleValidation($entity, EntityValidator::ACTION_UPDATE);

            if( !$validation_result->isValid() ){
                // todo: temporary solution
                // todo: need to rework ajax + response to support AjaxResponse + validation errors message + js logic
                $message = $this->translator->translate('responses.repositories.recordUpdateFail');
                return new Response($message, 500);
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
     * @param array $columns_names
     */
    public static function removeHelperColumnsFromView(array &$columns_names) {
        $columns_to_remove = ['deleted', 'delete'];

        foreach ($columns_to_remove as $column_to_remove) {
            $key = array_search($column_to_remove, $columns_names);

            if (!is_null($key) && $key) {
                unset($columns_names[$key]);
            }

        }
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
     * @param string $table_name
     * @return array
     */
    public function getColumnsNamesForTableName(string $table_name): array
    {
        $schema_manager = $this->entity_manager->getConnection()->getSchemaManager();
        $columns        = $schema_manager->listTableColumns($table_name);

        $columns_names = [];
        foreach($columns as $column){
            $columns_names[] = $column->getName();
        }

        return $columns_names;
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
        $all_related_entities = [];

        $class     = get_class($entity);
        $is_entity = self::isEntity($entity);

        if( !$is_entity ){
            throw new Exception("Tried to get related entities on non entity class: {$class}");
        }

        $class_meta            = $this->entity_manager->getClassMetadata($class);
        $associations_mappings = $class_meta->getAssociationMappings();

        foreach( $associations_mappings as $association_mapping ){
            $field_name = $association_mapping[self::KEY_CLASS_META_RELATED_ENTITY_FIELD_NAME];

            $method_name = "get" .  ucfirst($field_name);

            if( !method_exists($entity, $method_name) ){
                continue;
            }

            $data_for_method_name = $entity->$method_name();
            if( empty($data_for_method_name) ){
                continue;
            }

            $related_entities     = $entity->$method_name()->getValues();
            $all_related_entities = array_merge($all_related_entities, $related_entities);
        }

        return $all_related_entities;
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
                $is_not_empty = !empty($value);
                return $is_not_empty;
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

                    $found_corresponding_notes_categories    = $this->myNotesCategoriesRepository->getNotDeletedCategoriesForParentIdAndName($name, $value);
                    $category_with_this_name_exist_in_parent = !empty($found_corresponding_notes_categories);

                    if ($category_with_this_name_exist_in_parent) {
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
                        $name      = $entity->getName();
                        $parent_id = $entity->getParentId();

                        // Trigger name check only if name has changed, icon could change for example
                        if( $value == $name ){
                            return true;
                        }

                        $found_corresponding_notes_categories    = $this->myNotesCategoriesRepository->getNotDeletedCategoriesForParentIdAndName($value, $parent_id);
                        $category_with_this_name_exist_in_parent = !empty($found_corresponding_notes_categories);

                        if ($category_with_this_name_exist_in_parent) {
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
        $default_message = $this->translator->translate('responses.general.invalidParameterValue');
        $default_code    = 400;

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

        return new Response($default_message, $default_code);
    }

    /**
     * This function changes the record before soft delete
     * @param string $repository_name
     * @param $record
     * @return MyContact
     */
    private function changeRecordDataBeforeSoftDelete(string $repository_name, $record) {

        switch( $repository_name ){
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

        if( $record instanceof MyContactGroup){
            $color            = $record->getColor();
            $normalized_color = str_replace("#", "", $color);

            $record->setColor($normalized_color);
        }

        return $record;
    }

    /**
     * This function will handle soft removal for related entities
     * @param object $entity
     * @return object
     * @throws Exception
     */
    private function handleCascadeSoftDeleteRelatedEntities($entity)
    {
        $class_name = get_class($entity);

        $this->entity_manager->beginTransaction();
        {

            if( $entity instanceof SoftDeletableEntityInterface ){
                $related_entities = $this->getAllRelatedEntities($entity);

                if( empty($related_entities) ){
                    $this->entity_manager->rollback();
                    return $entity;
                }

                foreach($related_entities as $related_entity){
                    if( $related_entity instanceof SoftDeletableEntityInterface ){
                        $related_entity->setDeleted(true);
                        $this->entity_manager->persist($related_entity);
                    }
                }

            }else{
                $this->entity_manager->rollback();
                throw new Exception("This entity ({$class_name}) does not implements soft delete interface");
            }

            $this->entity_manager->flush();
        }
        $this->entity_manager->commit();

        return $entity;
    }


    /**
     * @param array $entity_data
     * @return object|null
     */
    private function getEntity(array $entity_data) {
        $entity = null;

        try {

            if (array_key_exists('namespace', $entity_data) && array_key_exists('id', $entity_data)) {
                $entity = $this->getDoctrine()->getRepository($entity_data['namespace'])->find($entity_data['id']);
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
        $record_class_name  = get_class($record);
        $class_meta         = $this->entity_manager->getClassMetadata($record_class_name);
        $table_name         = $class_meta->getTableName();
        $is_record_entity   = self::isEntity($record);

        if( !$is_record_entity ){
            return false;
        }

        # Second thing is that some tables have relation to self without foreign key - this must be checked separately
        $parent_keys       = ['parent', 'parent_id', 'parentId', 'parentID'];
        $has_self_relation = false;

        foreach ($parent_keys as $key) {

            if (property_exists($record, $key)) {
                $child_record      = $repository->findBy([$key => $record->getId(), self::ENTITY_PROPERTY_DELETED => 0]);
                $has_self_relation = true;
            }

            if (
                isset($child_record)
                &&  !empty($child_record)
                &&  (
                    (
                            method_exists($child_record, self::ENTITY_GET_DELETED_METHOD_NAME)
                        &&  !$child_record->getDeleted()
                    )
                    ||
                    (
                            method_exists($child_record, self::ENTITY_IS_DELETED_METHOD_NAME)
                        &&  !$child_record->isDeleted()
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

        $columns_names = $this->getColumnsNamesForTableName($table_name);

        foreach( $columns_names as $column_name ){
            # we have a child so we can remove it
            if( strstr($column_name, "_id") && !$has_self_relation ){
                return false;
            }
        }

        # we have parent so we need to check what's the state of all of his children
        # we have to find which fields are relational ones

        $related_entities = $this->getAllRelatedEntities($record);

        foreach( $related_entities as $related_entity ){

            if( method_exists($related_entity, self::ENTITY_GET_DELETED_METHOD_NAME) ){
                $is_deleted = $related_entity->getDeleted();

                if(!$is_deleted){
                    return true;
                }
            }

            if( method_exists($related_entity, self::ENTITY_IS_DELETED_METHOD_NAME) ){
                $is_deleted = $related_entity->isDeleted();

                if(!$is_deleted){
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

        if( !is_object($entity) ) {
            $message = $this->translator->translate("exceptions.general.providedEntityIsNotAnObject");
            throw new Exception($message);
        }

        $class_name = get_class($entity);

        switch( $class_name ){
            case MyIssue::class:
            case MyTodo::class:
                {
                    $this->handleCascadeSoftDeleteRelatedEntities($entity);
                }
                break;

            default:
                // do nothing
                // no break
        }

        return $entity;
    }

}
