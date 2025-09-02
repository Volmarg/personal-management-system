<?php

namespace App\Controller\Modules\Notes;

use App\Entity\Modules\Notes\MyNotes;
use App\Repository\Modules\Notes\MyNotesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyNotesController extends AbstractController {

    public function __construct(
        private readonly MyNotesRepository $myNotesRepository
    ) {
    }

    /**
     * @param array $categoriesIds
     * @return MyNotes[]
     */
    public function getNotesByCategoriesIds(array $categoriesIds): array
    {
        return $this->myNotesRepository->getNotesByCategoriesIds($categoriesIds);
    }

    /**
     * @return MyNotes[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->myNotesRepository->findAllNotDeleted();
    }
}
