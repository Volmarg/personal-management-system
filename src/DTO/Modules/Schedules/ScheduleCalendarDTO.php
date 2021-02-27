<?php

namespace App\DTO\Modules\Schedules;

/**
 * Class ScheduleCalendarDTO
 */
class ScheduleCalendarDTO {

    const KEY_ID                    = 'id';
    const KEY_NAME                  = 'name';
    const KEY_COLOR                 = 'color';
    const KEY_BACKGROUND_COLOR      = 'backgroundColor';
    const KEY_DRAG_BACKGROUND_COLOR = 'dragBackgroundColor';
    const KEY_BORDER_COLOR          = 'borderColor';
    const KEY_DELETED               = 'deleted';
    const KEY_ICON                  = 'icon';

    /**
     * @var int $id
     */
    private int $id;

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string $color
     */
    private string $color;

    /**
     * @var string $backgroundColor
     */
    private string $backgroundColor;

    /**
     * @var string $dragBackgroundColor
     */
    private string $dragBackgroundColor;

    /**
     * @var string $borderColor
     */
    private string $borderColor;

    /**
     * @var bool $deleted
     */
    private bool $deleted;

    /**
     * @var string $icon
     */
    private string $icon;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    /**
     * @param string $backgroundColor
     */
    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @return string
     */
    public function getDragBackgroundColor(): string
    {
        return $this->dragBackgroundColor;
    }

    /**
     * @param string $dragBackgroundColor
     */
    public function setDragBackgroundColor(string $dragBackgroundColor): void
    {
        $this->dragBackgroundColor = $dragBackgroundColor;
    }

    /**
     * @return string
     */
    public function getBorderColor(): string
    {
        return $this->borderColor;
    }

    /**
     * @param string $borderColor
     */
    public function setBorderColor(string $borderColor): void
    {
        $this->borderColor = $borderColor;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
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
     * Return current object as string
     *
     * @return string
     */
    public function toJson(): string
    {
        $dataArray = [
            self::KEY_ID                    => $this->getId(),
            self::KEY_NAME                  => $this->getName(),
            self::KEY_COLOR                 => $this->getColor(),
            self::KEY_BACKGROUND_COLOR      => $this->getBackgroundColor(),
            self::KEY_DRAG_BACKGROUND_COLOR => $this->getDragBackgroundColor(),
            self::KEY_BORDER_COLOR          => $this->getBorderColor(),
            self::KEY_DELETED               => $this->isDeleted(),
            self::KEY_ICON                  => $this->getIcon(),
        ];

        return json_encode($dataArray);
    }

}