<?php

namespace App\Twig\Modules\Notes;

use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Core\Application;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyNotes extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var MyNotesController $my_notes_controller
     */
    private $my_notes_controller;

    public function __construct(Application $app, MyNotesController $my_notes_controller) {
        $this->app = $app;
        $this->my_notes_controller = $my_notes_controller;
    }

    public function getFunctions() {
        return [
            new TwigFunction('hasCategoryFamilyVisibleNotes', [$this, 'hasCategoryFamilyVisibleNotes']),
        ];
    }

    /**
     * @param string $category_id
     * @return bool
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function hasCategoryFamilyVisibleNotes(string $category_id) {
        $hasCategoryFamilyVisibleNotes = $this->my_notes_controller->hasCategoryFamilyVisibleNotes($category_id);
        return $hasCategoryFamilyVisibleNotes;
    }

}