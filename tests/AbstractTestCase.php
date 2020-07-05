<?php

namespace App\Tests;

use App\Controller\Core\Env;
use App\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractTestCase extends TestCase {

    /**
     * @var ContainerInterface $container
     */
    private $container;

    protected function setUp(): void
    {
        $kernel = new Kernel(Env::APP_ENV_TEST, true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

}