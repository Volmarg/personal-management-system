<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\DTO\ParentChildDTO;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyNotesCategoriesController extends AbstractController {

    const CATEGORY_ID  = "category_id";
    const CHILDRENS_ID = "childrens_id";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var MyNotesController $my_notes_controller
     */
    private $my_notes_controller;

    public function __construct(Application $app, MyNotesController $my_notes_controller) {
        $this->app                 = $app;
        $this->my_notes_controller = $my_notes_controller;
    }

    /**
     * This function returns array of relations between categories (parent/child)
     * @return ParentChildDTO[]
     */
    public function buildParentsChildrenCategoriesHierarchy(): array
    {
        $categories_depths      = $this->buildCategoriesDepths();
        $parents_children_dtos  = [];
        $skipped_categories_ids = [];

        foreach( $categories_depths as $category_id => $depth ){

            $category     = $this->app->repositories->myNotesCategoriesRepository->find($category_id);
            $category_id  = $category->getId();

            $child_categories_ids = $this->app->repositories->myNotesCategoriesRepository->getChildrenCategoriesIdsForCategoriesIds([$category_id]);
            $parent_child_dto     = $this->buildParentChildDtoForHierarchy($category, $depth);

            //if we have a children then we already added it to parent so we don't want it as separated being
            $skipped_categories_ids = array_merge($skipped_categories_ids, $child_categories_ids);

            if( in_array($category_id, $skipped_categories_ids) ){
                continue;
            }

            $parents_children_dtos[] = $parent_child_dto;
        }

        return $parents_children_dtos;
    }

    /**
     * Returns the categories that are visible, have notes, are not deleted or have family tree inside with same rules
     * @return array
     * @throws DBALException
     * 
     */
    public function getAccessibleCategories(): array
    {
        $all_categories        = $this->app->repositories->myNotesCategoriesRepository->getCategories();
        $accessible_categories = [];

        foreach ($all_categories as $key => $result) {
            $category_id = $result[self::CATEGORY_ID];

            // check if this category is accessible
            if( !$this->my_notes_controller->hasCategoryFamilyVisibleNotes($category_id)){
                unset($all_categories[$key]);
                continue;
            }

            $accessible_categories[$category_id] = $result;

            if (!is_null($all_categories[$key][self::CHILDRENS_ID])) {
                $accessible_categories[$category_id][self::CHILDRENS_ID] = explode(',', $all_categories[$key][self::CHILDRENS_ID]);
            }

            // check if children categories are accessible
            if( !array_key_exists(self::CHILDRENS_ID, $accessible_categories[$category_id]) ) {
                continue;
            }

            $children_ids = $accessible_categories[$category_id][self::CHILDRENS_ID];

            if( is_null($children_ids) ){
                continue;
            }

            foreach( $children_ids as $index => $child_id ){
                $is_child_accessible = $this->my_notes_controller->hasCategoryFamilyVisibleNotes($child_id);

                if( !$is_child_accessible ){
                    unset($accessible_categories[$category_id][self::CHILDRENS_ID][$index]);
                }
            }
        }

        return $accessible_categories;
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function getAllNotesCategories(){
        $all_categories = $this->app->repositories->myNotesCategoriesRepository->getCategories();
        return $all_categories;
    }

    /**
     * @param string $name
     * @param string $category_id
     * @return bool
     */
    public function hasCategoryChildWithThisName(string $name, ?string $category_id): bool
    {
        $found_corresponding_notes_categories    = $this->app->repositories->myNotesCategoriesRepository->getNotDeletedCategoriesForParentIdAndName($name, $category_id);
        $category_with_this_name_exist_in_parent = !empty($found_corresponding_notes_categories);

        return $category_with_this_name_exist_in_parent;
    }

    /**
     * Recursive call must be used here as category can have children and these children can also have children and so on.
     * @param MyNotesCategories $category
     * @param int $depth
     * @return ParentChildDTO
     */
    private function buildParentChildDtoForHierarchy(MyNotesCategories $category, int $depth): ParentChildDTO
    {
        $parent_child_dtos = [];

        $category_id   = $category->getId();
        $category_name = $category->getName();

        $child_categories = $this->app->repositories->myNotesCategoriesRepository->getChildrenCategoriesForCategoriesIds([$category_id]);

        foreach($child_categories as $child_category){
            $child_depth         = $depth +1;
            $parent_child_dto    = $this->buildParentChildDtoForHierarchy($child_category, $child_depth);
            $parent_child_dtos[] = $parent_child_dto;
        }

        $parent_child_dto = new ParentChildDTO();
        $parent_child_dto->setType(ModulesController::MODULE_ENTITY_NOTES_CATEGORY);
        $parent_child_dto->setId($category_id);
        $parent_child_dto->setName($category_name);
        $parent_child_dto->setDepth($depth);
        $parent_child_dto->setChildren($parent_child_dtos);

        return $parent_child_dto;
    }

    /**
     * Build array where key is categoryId and value is depth level
     * @return array
     */
    private function buildCategoriesDepths(): array
    {
        $notes_categories  = $this->app->repositories->myNotesCategoriesRepository->findAllNotDeleted();
        $categories_depths = [];

        foreach( $notes_categories as $category ){
            $depth       = 0;
            $category_id = $category->getId();

            $has_parent                 = !empty($category->getParentId());
            $currently_checked_category = $category;
            while( $has_parent ){
                $parent_id = $currently_checked_category->getParentId();

                if( empty($parent_id) ){
                    break;
                }

                $parent_category            = $this->app->repositories->myNotesCategoriesRepository->find($parent_id);
                $currently_checked_category = $parent_category;

                $depth++;
            }

            $categories_depths[$category_id] = $depth;
        }

        return $categories_depths;
    }

}
