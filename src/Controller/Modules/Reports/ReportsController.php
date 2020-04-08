<?php
namespace App\Controller\Modules\Reports;

use App\Controller\Utils\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportsController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

}