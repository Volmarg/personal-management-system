<?php


namespace App\Services\Session;


use App\DTO\User\SessionDataDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Provides a way of setting user related data in session
 */
class SessionsService extends AbstractController {

    public function __construct(private readonly SessionInterface $session)
    {
    }

    /**
     * Sets or updates the user data in session
     *
     * @param int    $userId
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function setForUser(int $userId, string $key, mixed $value): void
    {
        $currentData = $this->getForUser($userId);
        $currentData->setBagValue($key, $value);
        $this->session->set($this->getUserKey($userId), $currentData->serialize());
    }

    /**
     * Provides session data dto for given user id, if not user data is set in session then returns
     * new instance of the session data
     *
     * @param int $userId
     *
     * @return SessionDataDTO
     */
    public function getForUser(int $userId): SessionDataDTO
    {
        $data = $this->session->get($this->getUserKey($userId));
        if (!$data) {
            $data = new SessionDataDTO();
            $data->setBagValue(SessionDataDTO::KEY_USER_ID, $userId);
            return $data;
        }

        return SessionDataDTO::deserialize($data);
    }

    /**
     * Wipes the whole user sessions data
     *
     * @param int $userId
     */
    public function clearForUser(int $userId): void
    {
        $this->session->remove($this->getUserKey($userId));
    }

    /**
     * Builds the keys that is used to store user data in session
     *
     * @param int $userId
     *
     * @return string
     */
    private function getUserKey(int $userId): string
    {
        return "user-{$userId}";
    }

}