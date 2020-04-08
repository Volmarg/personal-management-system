<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Utils\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyNotesCategoriesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
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

}
