<?php

namespace App\Services\External;

use App\DTO\NotificationDto;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DiscordService
{

    /**
     * @var Client $client
     */
    private readonly Client $client;


    public function __construct()
    {
        $this->client = new Client([]);
    }

    /**
     * @param NotificationDto $notificationDto
     *
     * @throws GuzzleException
     */
    public function sendWebhookMessage(NotificationDto $notificationDto): void
    {
        $this->client->post($notificationDto->getUrl(), [
            'json'    => [
                'username' => $notificationDto->getUsername(),
                'embeds'   => [
                    [
                        'title'       => $notificationDto->getTitle(),
                        'description' => $notificationDto->getMessage(),
                    ],
                ],
            ],
            'headers' => [
                'Content-Type application/json; charset=utf-8',
            ],
        ]);
    }
}