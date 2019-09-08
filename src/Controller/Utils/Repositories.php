<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:02
 */

namespace App\Controller\Utils;


use App\Entity\Modules\Job\MyJobHolidays;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use App\Entity\Modules\Job\MyJobSettings;
use App\Repository\FilesTagsRepository;
use App\Repository\Modules\Achievements\AchievementRepository;
use App\Repository\Modules\Car\MyCarRepository;
use App\Repository\Modules\Car\MyCarSchedulesTypesRepository;
use App\Repository\Modules\Contacts\MyContactsGroupsRepository;
use App\Repository\Modules\Contacts\MyContactsRepository;
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Repository\Modules\Goals\MyGoalsRepository;
use App\Repository\Modules\Goals\MyGoalsSubgoalsRepository;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use App\Repository\Modules\Job\MyJobHolidaysPoolRepository;
use App\Repository\Modules\Job\MyJobHolidaysRepository;
use App\Repository\Modules\Job\MyJobSettingsRepository;
use App\Repository\Modules\Notes\MyNotesRepository;
use App\Repository\Modules\Notes\MyNotesCategoriesRepository;
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use App\Repository\Modules\Payments\MyPaymentsOwedRepository;
use App\Repository\Modules\Payments\MyPaymentsProductRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\Modules\Shopping\MyShoppingPlansRepository;
use App\Repository\Modules\Travels\MyTravelsIdeasRepository;
use App\Repository\UserRepository;
use App\Services\Exceptions\ExceptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class Repositories extends AbstractController {

    const ACHIEVEMENT_REPOSITORY_NAME               = 'AchievementRepository';
    const MY_NOTES_REPOSITORY_NAME                  = 'MyNotesRepository';
    const MY_NOTES_CATEGORIES_REPOSITORY_NAME       = 'MyNotesCategoriesRepository';
    const MY_CAR_REPOSITORY_NAME                    = 'MyCarRepository';
    const MY_JOB_AFTERHOURS_REPOSITORY_NAME         = 'MyJobAfterhoursRepository';
    const MY_PAYMENTS_MONTHLY_REPOSITORY_NAME       = 'MyPaymentsMonthlyRepository';
    const MY_PAYMENTS_PRODUCTS_REPOSITORY_NAME      = 'MyPaymentsProductRepository';
    const MY_PAYMENTS_SETTINGS_REPOSITORY_NAME      = 'MyPaymentsSettingsRepository';
    const MY_SHOPPING_PLANS_REPOSITORY_NAME         = 'MyShoppingPlansRepository';
    const MY_TRAVELS_IDEAS_REPOSITORY_NAME          = 'MyTravelsIdeasRepository';
    const INTEGRATIONS_RESOURCES_REPOSITORY_NAME    = 'IntegrationResourceRepository';
    const MY_CONTACTS_REPOOSITORY_NAME              = 'MyContactsRepository';
    const MY_CONTACTS_GROUPS_REPOSITORY_NAME        = 'MyContactsGroupsRepository';
    const MY_PASSWORDS_REPOSITORY_NAME              = 'MyPasswordsRepository';
    const MY_PASSWORDS_GROUPS_REPOSITORY_NAME       = 'MyPasswordsGroupsRepository';
    const USER_REPOSITORY                           = 'UserRepository';
    const MY_GOALS_REPOSITORY_NAME                  = 'MyGoalsRepository';
    const MY_SUBGOALS_REPOSITORY_NAME               = 'MyGoalsSubgoalsRepository';
    const MY_GOALS_PAYMENTS_REPOSITORY_NAME         = 'MyGoalsPaymentsRepository';
    const MY_CAR_SCHEDULES_TYPES_REPOSITORY_NAME    = 'MyCarSchedulesTypesRepository';
    const MY_JOB_HOLIDAYS_REPOSITORY_NAME           = 'MyJobHolidaysRepository';
    const MY_JOB_HOLIDAYS_POOL_REPOSITORY_NAME      = 'MyJobHolidaysPoolRepository';
    const MY_JOB_SETTINGS_REPOSITORY_NAME           = 'MyJobSettingsRepository';
    const MY_PAYMENTS_OWED_REPOSITORY_NAME          = 'MyPaymentsOwedRepository';
    const FILE_TAGS_REPOSITORY                      = 'FilesTagsRepository';

    const PASSWORD_FIELD                            = 'password';
    /**
     * @var MyNotesRepository $myNotesRepository
     */
    public $myNotesRepository;

    /**
     * @var MyCarRepository
     */
    public $myCarRepository;

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
     * @var MyContactsRepository
     */
    public $myContactsRepository;

    /**
     * @var MyContactsGroupsRepository
     */
    public $myContactsGroupsRepository;

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
     * @var MyCarSchedulesTypesRepository
     */
    public $myCarSchedulesTypesRepository;

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

    public function __construct(
        MyNotesRepository               $myNotesRepository,
        MyCarRepository                 $myCarRepository,
        AchievementRepository           $myAchievementsRepository,
        MyJobAfterhoursRepository       $myJobAfterhoursRepository,
        MyPaymentsMonthlyRepository     $myPaymentsMonthlyRepository,
        MyPaymentsProductRepository     $myPaymentsProductRepository,
        MyShoppingPlansRepository       $myShoppingPlansRepository,
        MyTravelsIdeasRepository        $myTravelIdeasRepository,
        MyPaymentsSettingsRepository    $myPaymentsSettingsRepository,
        MyNotesCategoriesRepository     $myNotesCategoriesRepository,
        MyContactsRepository            $myContactsRepository,
        MyContactsGroupsRepository      $myContactsGroupsRepository,
        MyPasswordsRepository           $myPasswordsRepository,
        MyPasswordsGroupsRepository     $myPasswordsGroupsRepository,
        UserRepository                  $userRepository,
        MyGoalsRepository               $myGoalsRepository,
        MyGoalsSubgoalsRepository       $myGoalsSubgoalsRepository,
        MyGoalsPaymentsRepository       $myGoalsPaymentsRepository,
        MyCarSchedulesTypesRepository   $myCarSchedulesTypesRepository,
        MyJobHolidaysRepository         $myJobHolidaysRepository,
        MyJobHolidaysPoolRepository     $myJobHolidaysPoolRepository,
        MyJobSettingsRepository         $myJobSettingsRepository,
        MyPaymentsOwedRepository        $myPaymentsOwedRepository,
        FilesTagsRepository             $filesTagsRepository
    ) {
        $this->myNotesRepository                = $myNotesRepository;
        $this->myCarRepository                  = $myCarRepository;
        $this->achievementRepository            = $myAchievementsRepository;
        $this->myJobAfterhoursRepository        = $myJobAfterhoursRepository;
        $this->myPaymentsMonthlyRepository      = $myPaymentsMonthlyRepository;
        $this->myPaymentsProductRepository      = $myPaymentsProductRepository;
        $this->myShoppingPlansRepository        = $myShoppingPlansRepository;
        $this->myTravelsIdeasRepository         = $myTravelIdeasRepository;
        $this->myPaymentsSettingsRepository     = $myPaymentsSettingsRepository;
        $this->myNotesCategoriesRepository      = $myNotesCategoriesRepository;
        $this->myContactsRepository             = $myContactsRepository;
        $this->myContactsGroupsRepository       = $myContactsGroupsRepository;
        $this->myPasswordsRepository            = $myPasswordsRepository;
        $this->myPasswordsGroupsRepository      = $myPasswordsGroupsRepository;
        $this->userRepository                   = $userRepository;
        $this->myGoalsRepository                = $myGoalsRepository;
        $this->myGoalsSubgoalsRepository        = $myGoalsSubgoalsRepository;
        $this->myGoalsPaymentsRepository        = $myGoalsPaymentsRepository;
        $this->myCarSchedulesTypesRepository    = $myCarSchedulesTypesRepository;
        $this->myJobHolidaysRepository          = $myJobHolidaysRepository;
        $this->myJobHolidaysPoolRepository      = $myJobHolidaysPoolRepository;
        $this->myJobSettingsRepository          = $myJobSettingsRepository;
        $this->myPaymentsOwedRepository         = $myPaymentsOwedRepository;
        $this->filesTagsRepository              = $filesTagsRepository;
    }

    /**
     * @param string $repository_name
     * @param $id
     * This is general method for all common record soft delete called from front
     * @param array $findByParams
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteById(string $repository_name, $id, array $findByParams = []) {
        try {

            $id         = $this->trimAndCheckId($id);
            $repository = $this->{lcfirst($repository_name)};
            $record     = $repository->find($id);

            if ($this->hasChildren($record, $repository)) {
                throw new \Exception('The record which You try to remove, is a parent of other record! Please remove children first!');
            }

            $record->setDeleted(1);

            $em = $this->getDoctrine()->getManager();

            $em->persist($record);
            $em->flush();

            return new JsonResponse('Record was deleted successfully', 200);
        } catch (\Exception | ExceptionRepository $er) {
            return new JsonResponse('Record could not been deleted', 500);
        }
    }

    /**
     * @param array $parameters
     * @param $entity
     * This is general method for all common record update called from front
     * @param array $findByParams
     * @return JsonResponse
     *
     * It's required that field for which You want to get entity has this example format in js ajax request:
     * 'category': {
     * "type": "entity",
     * 'namespace': 'App\\Entity\\MyNotesCategories',
     * 'id': $(noteCategoryId).val(),
     * },
     */
    public function update(array $parameters, $entity, array $findByParams = []) {

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

                if ($parameter === static::PASSWORD_FIELD && !$this->isPasswordValueValid($value)) {
                    return new JsonResponse('For Your own safety! Password change has been canceled due to some field validations!', 500);
                }

                if (is_array($value)) {
                    if (array_key_exists('type', $value) && $value['type'] == 'entity') {
                        $value = $this->getEntity($value);
                    }
                }

                $methodName = 'set' . ucfirst($parameter);
                $methodName = (strstr($methodName, '_id') ? str_replace('_id', 'Id', $methodName) : $methodName);

                if (is_object($value)) {
                    $entity->$methodName($value);
                    continue;
                }
                $entity->$methodName($value);

            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return new JsonResponse('Record has been updated', 200);
        } catch (ExceptionRepository $er) {
            return new JsonResponse('Record could not been updated', 500);
        }
    }

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

    private function hasChildren($record, $repository) {
        $parent_keys = ['parent', 'parent_id', 'parentId', 'parentID'];
        $result = false;

        foreach ($parent_keys as $key) {

            if (property_exists($record, $key)) {
                $child_record = $repository->findBy([$key => $record->getId(), 'deleted' => 0]);
            }

            if (isset($child_record) && !empty($child_record)) {
                $result = true;
                break;
            }

        }

        return $result;
    }

    public static function removeHelperColumnsFromView(array &$columns_names) {
        $columns_to_remove = ['deleted', 'delete'];

        foreach ($columns_to_remove as $column_to_remove) {
            $key = array_search($column_to_remove, $columns_names);

            if (!is_null($key) && $key) {
                unset($columns_names[$key]);
            }

        }
    }

    private function isPasswordValueValid($value) {
        return !empty($value);
    }

    /**
     * This function trims id and rechecks if it's int
     * The problem is that js keeps getting all the whitespaces and new lines in to many places....
     *
     * @param $id
     * @return string
     * @throws \Exception
     */
    private function trimAndCheckId($id){
        $id = (int) trim($id);

        if (!is_numeric($id)) {
            throw new \Exception("Incorrect id! Expected numeric value, received: $id");
        }

        return $id;
    }
}