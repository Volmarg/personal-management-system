<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Controller\Core\Application;
use App\Entity\System\LockedResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyNotesController extends AbstractController {

    const KEY_CATEGORY_ID   = 'category_id';
    const KEY_CATEGORY_NAME = "category_name";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var LockedResourceController $locked_resource_controller
     */
    private $locked_resource_controller;

    public function __construct(Application $app, LockedResourceController $locked_resource_controller) {
        $this->app = $app;
        $this->locked_resource_controller = $locked_resource_controller;
    }

    /**
     * @param string $category_id
     * @return bool
     * 
     */
    public function hasCategoryFamilyVisibleNotes(string $category_id)
    {

        # 1. Some notes might just be empty, but if they have children then it cannot be hidden if some child has active note
        $has_category_family_active_note = false;
        $categories_ids = [$category_id];

        while( !$has_category_family_active_note ){

                $have_categories_notes = $this->app->repositories->myNotesCategoriesRepository->haveCategoriesNotes($categories_ids);

                if( $have_categories_notes ){

                    $notes = $this->app->repositories->myNotesRepository->getNotesByCategory($categories_ids);

                    # 2. Check lock and make sure that there are some notes visible
                    foreach( $notes as $index => $note ){
                        $note_id = $note->getId();
                        if( !$this->locked_resource_controller->isAllowedToSeeResource($note_id, LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_NOTES, false)         ){
                            unset($notes[$index]);
                        }
                    }

                    if( !empty($notes) ){
                        $has_category_family_active_note = true;
                        break;
                    }

                }

                $have_categories_children = $this->app->repositories->myNotesCategoriesRepository->haveCategoriesChildren($categories_ids);

                if( !$have_categories_children ){
                    break;
                }

                $categories_ids = $this->app->repositories->myNotesCategoriesRepository->getChildrenCategoriesIdsForCategoriesIds($categories_ids);

                if( empty($categories_ids) ){
                    break;
                }
        }

        if( $has_category_family_active_note ){
            return true;
        }

        return false;
    }

}
