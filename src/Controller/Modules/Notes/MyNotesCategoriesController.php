<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\DTO\ParentChildDTO;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\System\LockedResource;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyNotesCategoriesController extends AbstractController {

    const CATEGORY_ID  = "category_id";
    const CHILDRENS_ID = "childrens_id";
    const CATEGORY     = "category";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var MyNotesController $myNotesController
     */
    private $myNotesController;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController  $lockedResourceController;

    public function __construct(Application $app, MyNotesController $myNotesController, LockedResourceController  $lockedResourceController) {
        $this->app                      = $app;
        $this->myNotesController        = $myNotesController;
        $this->lockedResourceController = $lockedResourceController;
    }

    /**
     * This function returns array of relations between categories (parent/child)
     * @return ParentChildDTO[]
     */
    public function buildParentsChildrenCategoriesHierarchy(): array
    {
        $categoriesDepths     = $this->buildCategoriesDepths();
        $parentsChildrenDtos  = [];
        $skippedCategoriesIds = [];

        foreach( $categoriesDepths as $categoryId => $depth ){

            $category   = $this->app->repositories->myNotesCategoriesRepository->find($categoryId);
            $categoryId = $category->getId();

            $childCategoriesIds = $this->app->repositories->myNotesCategoriesRepository->getChildrenCategoriesIdsForCategoriesIds([$categoryId]);
            $parentChildDto     = $this->buildParentChildDtoForHierarchy($category, $depth);

            //if we have a children then we already added it to parent so we don't want it as separated being
            $skippedCategoriesIds = array_merge($skippedCategoriesIds, $childCategoriesIds);

            if( in_array($categoryId, $skippedCategoriesIds) ){
                continue;
            }

            $parentsChildrenDtos[] = $parentChildDto;
        }

        // sort alphabetically by name
        uasort($parentsChildrenDtos, fn(ParentChildDTO $currentElement, ParentChildDTO $nextElement) =>
            $currentElement->getName() > $nextElement->getName()
        );

        return $parentsChildrenDtos;
    }

    /**
     * Returns the categories that:
     * - are visible,
     * - have notes,
     * - are not deleted or have family tree inside with same rules
     * - are not locked
     l
     *
     * @return array
     * @throws DBALException
     * @throws Exception
     *
     */
    public function getAccessibleCategories(): array
    {
        $isAllowedToSeeResourceStmt = $this->app->repositories->lockedResourceRepository->buildIsLockForRecordTypeAndTargetStatement();
        $haveCategoriesNotesStmt    = $this->app->repositories->myNotesCategoriesRepository->buildHaveCategoriesNotesStatement();

        $allCategories                  = $this->app->repositories->myNotesCategoriesRepository->getCategories();
        $accessibleCategories           = [];

        foreach ($allCategories as $key => $result) {
            $categoryId = $result[self::CATEGORY_ID];

            // check if this category is accessible
            if( !$this->myNotesController->hasCategoryFamilyVisibleNotes($categoryId, $isAllowedToSeeResourceStmt, $haveCategoriesNotesStmt)){
                unset($allCategories[$key]);
                continue;
            }

            // check if category is locked (parent)
            if( !$this->lockedResourceController->isAllowedToSeeResource($categoryId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_ENTITY_NOTES_CATEGORY, false, $isAllowedToSeeResourceStmt) ){
                unset($allCategories[$key]);
                continue;
            }

            $accessibleCategories[$categoryId] = $result;

            if (!is_null($allCategories[$key][self::CHILDRENS_ID])) {
                $accessibleCategories[$categoryId][self::CHILDRENS_ID] = explode(',', $allCategories[$key][self::CHILDRENS_ID]);
            }

            // check if children categories are accessible
            if( !array_key_exists(self::CHILDRENS_ID, $accessibleCategories[$categoryId]) ) {
                continue;
            }

            $childrenIds = $accessibleCategories[$categoryId][self::CHILDRENS_ID];
            if( is_null($childrenIds) ){
                continue;
            }

            foreach( $childrenIds as $index => $childId ){
                $isChildAccessible = true;
                if(
                        !$this->myNotesController->hasCategoryFamilyVisibleNotes($childId, $isAllowedToSeeResourceStmt, $haveCategoriesNotesStmt)
                    ||  !$this->lockedResourceController->isAllowedToSeeResource($childId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_ENTITY_NOTES_CATEGORY, false, $isAllowedToSeeResourceStmt)
                ){
                    $isChildAccessible = false;
                }

                if( !$isChildAccessible ){
                    unset($accessibleCategories[$categoryId][self::CHILDRENS_ID][$index]);
                }
            }
        }

        // sort alphabetically by category name
        uasort($accessibleCategories, fn(array $currentCategory, array $nextCategory) =>
            $currentCategory[self::CATEGORY] >= $nextCategory[self::CATEGORY]
        );
        return $accessibleCategories;
    }

    /**
     * @param string $name
     * @param string $categoryId
     * @return bool
     */
    public function hasCategoryChildWithThisName(string $name, ?string $categoryId): bool
    {
        $foundCorrespondingNotesCategories = $this->app->repositories->myNotesCategoriesRepository->getNotDeletedCategoriesForParentIdAndName($name, $categoryId);
        $categoryWithThisNameExistInParent = !empty($foundCorrespondingNotesCategories);

        return $categoryWithThisNameExistInParent;
    }

    /**
     * Recursive call must be used here as category can have children and these children can also have children and so on.
     * @param MyNotesCategories $category
     * @param int $depth
     * @return ParentChildDTO
     */
    private function buildParentChildDtoForHierarchy(MyNotesCategories $category, int $depth): ParentChildDTO
    {
        $parentChildDtos = [];

        $categoryId   = $category->getId();
        $categoryName = $category->getName();

        $childCategories = $this->app->repositories->myNotesCategoriesRepository->getChildrenCategoriesForCategoriesIds([$categoryId]);

        foreach($childCategories as $childCategory){
            $childDepth        = $depth +1;
            $parentChildDto    = $this->buildParentChildDtoForHierarchy($childCategory, $childDepth);
            $parentChildDtos[] = $parentChildDto;
        }

        $parentChildDto = new ParentChildDTO();
        $parentChildDto->setType(ModulesController::MODULE_ENTITY_NOTES_CATEGORY);
        $parentChildDto->setId($categoryId);
        $parentChildDto->setName($categoryName);
        $parentChildDto->setDepth($depth);
        $parentChildDto->setChildren($parentChildDtos);

        return $parentChildDto;
    }

    /**
     * Build array where key is categoryId and value is depth level
     * @return array
     */
    private function buildCategoriesDepths(): array
    {
        $notesCategories  = $this->app->repositories->myNotesCategoriesRepository->findAllNotDeleted();
        $categoriesDepths = [];

        foreach( $notesCategories as $category ){
            $depth      = 0;
            $categoryId = $category->getId();

            $hasParent                = !empty($category->getParentId());
            $currentlyCheckedCategory = $category;
            while( $hasParent ){
                $parentId = $currentlyCheckedCategory->getParentId();

                if( empty($parentId) ){
                    break;
                }

                $parentCategory           = $this->app->repositories->myNotesCategoriesRepository->find($parentId);
                $currentlyCheckedCategory = $parentCategory;

                $depth++;
            }

            $categoriesDepths[$categoryId] = $depth;
        }
        asort($categoriesDepths); // required to prevent child categories with depth 1+ being added to root (depth 0)
        return $categoriesDepths;
    }

    /**
     * Will return one entity for given id, otherwise returns null if nothing is found
     *
     * @param int $id
     * @return MyNotesCategories|null
     */
    public function findOneById(int $id): ?MyNotesCategories
    {
        return $this->app->repositories->myNotesCategoriesRepository->findOneById($id);
    }

    /**
     * @return MyNotesCategories[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->app->repositories->myNotesCategoriesRepository->findAllNotDeleted();
    }

}
