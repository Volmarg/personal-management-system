<?php

namespace App\DTO\Modules\Schedules;

use App\Entity\Modules\Schedules\Schedule;

class ScheduleDTO {

    const KEY_ID          = 'id';
    const KEY_TITLE       = 'title';
    const KEY_ALL_DAY     = 'allDay';
    const KEY_START       = 'start';
    const KEY_END         = 'end';
    const KEY_CATEGORY    = 'category';
    const KEY_LOCATION    = 'location';
    const KEY_CALENDAR_ID = 'calendarId';

    /**
     * @var string $id
     */
    private string $id;

    /**
     * @var string $title
     */
    private string $title;

    /**
     * @var string $allDay
     */
    private string $allDay;

    /**
     * @var string $start
     */
    private string $start;

    /**
     * @var string $end
     */
    private string $end;

    /**
     * @var string $category
     */
    private string $category;

    /**
     * @var string $location
     */
    private string $location;

    /**
     * @var string $calendarId
     */
    private string $calendarId;

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
     */
    public function getAllDay(): string
    {
        return $this->allDay;
    }

    /**
     * @param string $allDay
     */
    public function setAllDay(string $allDay): void
    {
        $this->allDay = $allDay;
    }

    /**
     * @return string
     */
    public function getStart(): string
    {
        return $this->start;
    }

    /**
     * @param string $start
     */
    public function setStart(string $start): void
    {
        $this->start = $start;
    }

    /**
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }

    /**
     * @param string $end
     */
    public function setEnd(string $end): void
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getCalendarId(): string
    {
        return $this->calendarId;
    }

    /**
     * @param string $calendarId
     */
    public function setCalendarId(string $calendarId): void
    {
        $this->calendarId = $calendarId;
    }

    /**
     * Will build json representation of dto
     */
    public function toJson()
    {
        $dataArray = [
            self::KEY_ID          => $this->getId(),
            self::KEY_TITLE       => $this->getTitle(),
            self::KEY_ALL_DAY     => $this->getAllDay(),
            self::KEY_START       => $this->getStart(),
            self::KEY_END         => $this->getEnd(),
            self::KEY_CATEGORY    => $this->getCategory(),
            self::KEY_LOCATION    => $this->getLocation(),
            self::KEY_CALENDAR_ID => $this->getCalendarId(),
        ];

        return json_encode($dataArray);
    }

    /**
     * Will build the dto from schedule entity
     *
     * @param Schedule $schedule
     * @return ScheduleDTO
     */
    public static function fromScheduleEntity(Schedule $schedule): ScheduleDTO
    {
        $dto = new ScheduleDTO();
        $dto->setId($schedule->getId());
        $dto->setAllDay($schedule->getAllDay());
        $dto->setCalendarId($schedule->getCalendar()->getId());
        $dto->setTitle($schedule->getTitle());
        $dto->setStart($schedule->getStart()->format("Y-m-d H:i:s"));
        $dto->setLocation($schedule->getLocation());
        $dto->setEnd($schedule->getEnd()->format("Y-m-d H:i:s"));
        $dto->setCategory($schedule->getCategory());

        return $dto;
    }
}