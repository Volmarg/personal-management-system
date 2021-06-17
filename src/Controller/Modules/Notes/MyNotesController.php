<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Controller\Core\Application;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\System\LockedResource;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Statement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyNotesController extends AbstractController {

    const KEY_CATEGORY_ID   = 'category_id';
    const KEY_CATEGORY_NAME = "category_name";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private $lockedResourceController;

    public function __construct(Application $app, LockedResourceController $lockedResourceController) {
        $this->app = $app;
        $this->lockedResourceController = $lockedResourceController;
    }

    /**
     * Checks is the whole notes family has any active notes at all
     *
     * @param string $checkedCategoryId
     * @param Statement|null $isAllowedToSeeResourceStmt
     * @param null $haveCategoriesNotesStmt
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function hasCategoryFamilyVisibleNotes(string $checkedCategoryId, Statement $isAllowedToSeeResourceStmt = null, $haveCategoriesNotesStmt = null): bool
    {
        if( is_null($haveCategoriesNotesStmt) ){
            $haveCategoriesNotesStmt = $this->app->repositories->myNotesCategoriesRepository->buildHaveCategoriesNotesStatement();
        }


        # 1. Some notes might just be empty, but if they have children then it cannot be hidden if some child has active note
        $hasCategoryFamilyActiveNote = false;
        $categoriesIds               = [$checkedCategoryId];

        while( !$hasCategoryFamilyActiveNote ){


                // it can be that the whole category itself is blocked, not only few notes inside
                foreach($categoriesIds as $idx => $categoryId){
                    if( !$this->lockedResourceController->isAllowedToSeeResource($categoryId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_ENTITY_NOTES_CATEGORY, false, $isAllowedToSeeResourceStmt) ){
                        unset($categoriesIds[$idx]);
                    }
                }

                $haveCategoriesNotes = $this->app->repositories->myNotesCategoriesRepository->executeHaveCategoriesNotesStatement($haveCategoriesNotesStmt, $categoriesIds);
                if( $haveCategoriesNotes ){

                    $notes = $this->app->repositories->myNotesRepository->getNotesByCategoriesIds($categoriesIds);

                    # 2. Check lock and make sure that there are some notes visible
                    foreach( $notes as $index => $note ){
                        $noteId = $note->getId();
                        if( !$this->lockedResourceController->isAllowedToSeeResource($noteId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_NOTES, false, $isAllowedToSeeResourceStmt) ){
                            unset($notes[$index]);
                        }
                    }

                    if( !empty($notes) ){
                        $hasCategoryFamilyActiveNote = true;
                    }

                }

                $haveCategoriesChildren = $this->app->repositories->myNotesCategoriesRepository->executeHaveCategoriesNotesStatement($haveCategoriesNotesStmt, $categoriesIds);
                if( !$haveCategoriesChildren ){
                    break;
                }

                $categoriesIds = $this->app->repositories->myNotesCategoriesRepository->getChildrenCategoriesIdsForCategoriesIds($categoriesIds);
                if( empty($categoriesIds) ){
                    break;
                }
        }

        if( $hasCategoryFamilyActiveNote ){
            return true;
        }

        return false;
    }

    /**
     * Returns one note for given id or null if nothing was found
     * @param int $id
     * @return MyNotes|null
     */
    public function getOneById(int $id): ?MyNotes
    {
        return $this->app->repositories->myNotesRepository->getOneById($id);
    }

    /**
     * @param array $categoriesIds
     * @return MyNotes[]
     */
    public function getNotesByCategoriesIds(array $categoriesIds): array
    {
        return $this->app->repositories->myNotesRepository->getNotesByCategoriesIds($categoriesIds);
    }

    /**
     * @return MyNotes[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->app->repositories->myNotesRepository->findAllNotDeleted();
    }
}
