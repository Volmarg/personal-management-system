<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingRepository")
 */
class Setting implements EntityInterface
{
    public const string DASHBOARD_WIDGET_GOAL_PROGRESS = "goalProgress";
    public const string DASHBOARD_WIDGET_GOAL_PAYMENTS = "goalPayments";
    public const string DASHBOARD_WIDGET_ISSUES        = "issues";
    public const string DASHBOARD_WIDGET_SCHEDULES     = "schedules";

    public const array ALL_DASHBOARD_WIDGETS = [
        self::DASHBOARD_WIDGET_GOAL_PROGRESS,
        self::DASHBOARD_WIDGET_GOAL_PAYMENTS,
        self::DASHBOARD_WIDGET_ISSUES,
        self::DASHBOARD_WIDGET_SCHEDULES,
    ];

    public const array ALL_NOTIFICATION_CONFIGS = [
        'webhook',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
