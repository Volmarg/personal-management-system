<?php

namespace App\Controller\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsOwed;
use App\Repository\Modules\Payments\MyPaymentsOwedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPaymentsOwedController extends AbstractController
{

    public function __construct(private readonly MyPaymentsOwedRepository $myPaymentsOwedRepository)
    {
    }

    /**
     * Will return one record or null if nothing was found
     *
     * @param int $id
     *
     * @return MyPaymentsOwed|null
     */
    public function findOneById(int $id): ?MyPaymentsOwed
    {
        return $this->myPaymentsOwedRepository->findOneById($id);
    }

}
