<?php

namespace App\Services\Session;

use App\Controller\Core\Application;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionsService
 * @package App\Services\Session
 */
class SessionsService extends AbstractController {

    const KEY_ENCRYPTION_KEY = "ENCRYPTION_KEY";

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
    protected $app = null;

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface {
        return $this->session;
    }

    /**
     * SessionsService constructor.
     * @param SessionInterface $session
     * @param Application|null $app
     */
    public function __construct(SessionInterface $session, ?Application $app) {
        $this->session = $session;
        $this->now     = new DateTime();
        $this->app     = $app;
    }

    /**
     * @param string $encryptionKey
     */
    public function setEncryptionKey(string $encryptionKey): void
    {
        $this->session->set(self::KEY_ENCRYPTION_KEY, $encryptionKey);
    }

    /**
     * @return bool
     */
    public function hasEncryptionKey(): bool
    {
        return $this->session->has(self::KEY_ENCRYPTION_KEY);
    }

    /**
     * @return mixed
     */
    public function getEncryptionKey()
    {
        return $this->session->get(self::KEY_ENCRYPTION_KEY);
    }
}