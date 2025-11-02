<?php

namespace App\Services\Module\Notes;

use App\Entity\Modules\Notes\MyNotesCategories;
use App\Repository\Modules\Notes\MyNotesCategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyNotesCategoriesService extends AbstractController {

    public function __construct(
        private readonly MyNotesCategoriesRepository $notesCategoriesRepository
    ) {
    }

    /**
     * @param string $name
     * @param string $categoryId
     * @return bool
     */
    public function hasCategoryChildWithThisName(string $name, ?string $categoryId): bool
    {
        $foundCorrespondingNotesCategories = $this->notesCategoriesRepository->getNotDeletedCategoriesForParentIdAndName($name, $categoryId);
        $categoryWithThisNameExistInParent = !empty($foundCorrespondingNotesCategories);

        return $categoryWithThisNameExistInParent;
    }


    /**
     * @return MyNotesCategories[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->notesCategoriesRepository->findAllNotDeleted();
    }

}
