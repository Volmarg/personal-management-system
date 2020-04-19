<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Core\Application;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
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
     * Build array where key is categoryId and value is depth level
     * @return array
     */
    public function buildCategoriesDepths(): array
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

    /**
     * Returns the categories that are visible, have notes, are not deleted or have family tree inside with same rules
     * @return array
     * @throws DBALException
     * @throws ExceptionDuplicatedTranslationKey
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

}
