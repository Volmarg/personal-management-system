<?php

namespace App\DTO\Modules\Schedules;

use DateTime;
use Exception;

/**
 * Class IncomingScheduleDTO
 */
class IncomingScheduleDTO {

    /**
     * @var string $id
     */
    private string $id;

    /**
     * @var string $reminderId
     */
    private string $reminderId;

    /**
     * @var string $title
     */
    private string $title = "";

    /**
     * @var string $date
     */
    private string $date;

    /**
     * @var int $daysDiff
     */
    private int $daysDiff;

    /**
     * @var string $scheduleType
     */
    private string $scheduleType = "";

    /**
     * @var string $icon
     */
    private string $icon = "";

    /**
     * @var string $body
     */
    private string $body = "";

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getDaysDiff(): int
    {
        return $this->daysDiff;
    }

    /**
     * @param int $daysDiff
     */
    public function setDaysDiff(int $daysDiff): void
    {
        $this->daysDiff = $daysDiff;
    }

    /**
     * @return string
     */
    public function getScheduleType(): string
    {
        return $this->scheduleType;
    }

    /**
     * @param string $scheduleType
     */
    public function setScheduleType(string $scheduleType): void
    {
        $this->scheduleType = $scheduleType;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getReminderId(): string
    {
        return $this->reminderId;
    }

    /**
     * @param string $reminderId
     */
    public function setReminderId(string $reminderId): void
    {
        $this->reminderId = $reminderId;
    }

}