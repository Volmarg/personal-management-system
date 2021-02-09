<?php

namespace App\Twig\Modules\Notes;

use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Core\Application;
use App\DTO\ParentChildDTO;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyNotes extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var MyNotesController $myNotesController
     */
    private $myNotesController;

    /**
     * @var MyNotesCategoriesController $myNotesCategoriesController
     */
    private $myNotesCategoriesController;

    public function __construct(Application $app, MyNotesController $myNotesController, MyNotesCategoriesController $myNotesCategoriesController) {
        $this->app                         = $app;
        $this->myNotesController           = $myNotesController;
        $this->myNotesCategoriesController = $myNotesCategoriesController;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getAccessibleNotesCategories',            [$this, 'getAccessibleNotesCategories']),
            new TwigFunction('hasCategoryFamilyVisibleNotes',           [$this, 'hasCategoryFamilyVisibleNotes']),
            new TwigFunction('isNotesCategoryActive',                   [$this, 'isNotesCategoryActive']),
            new TwigFunction('buildParentsChildrenCategoriesHierarchy', [$this, 'buildParentsChildrenCategoriesHierarchy']),
            new TwigFunction('getAllNotesCategories',                   [$this, 'getAllNotesCategories']),
        ];
    }

    /**
     * @param string $categoryId
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasCategoryFamilyVisibleNotes(string $categoryId) {
        $hasCategoryFamilyVisibleNotes = $this->myNotesController->hasCategoryFamilyVisibleNotes($categoryId);
        return $hasCategoryFamilyVisibleNotes;
    }

    /**
     * Based on count of notes in category (for recursive menu mostly)
     * @param int $categoryId
     * @param string $type
     * @return bool
     * @throws DBALException
     */
    public function isNotesCategoryActive(int $categoryId, string $type){
        switch ($type) {
            case 'MyNotes':
                return (bool) $this->app->repositories->myNotesRepository->countNotesInCategoryByCategoryId($categoryId);
            default:
                return false;
        }
    }

    /**
     * @return array
     *
     * @throws DBALException
     * @throws Exception
     */
    public function getAccessibleNotesCategories() {
        $accessibleCategories = $this->myNotesCategoriesController->getAccessibleCategories();
        return $accessibleCategories;
    }

    /**
     * @return ParentChildDTO[]
     */
    public function buildParentsChildrenCategoriesHierarchy(): array {
        $parentsChildrenDtos = $this->myNotesCategoriesController->buildParentsChildrenCategoriesHierarchy();
        return $parentsChildrenDtos;
    }

}