<?php


namespace App\Services\Session;


use App\Controller\Core\Application;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



/**
 * Class SessionsService
 * @package App\Services\Session
 */
class SessionsService extends AbstractController {

    /**
     * @var SessionInterface $session
     */
    protected $session;

    /**
     * @var DateTime | null
     */
    protected $now = null;

    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface {
        return $this->session;
    }

    /**
     * SessionsService constructor.
     * @param SessionInterface $session
     * @param Application $app
     * @throws Exception
     */
    public function __construct(SessionInterface $session, Application $app) {
        $this->session = $session;
        $this->now     = new DateTime();
        $this->app     = $app;
    }

}