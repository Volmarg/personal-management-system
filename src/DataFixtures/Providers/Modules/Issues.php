<?php
namespace App\DataFixtures\Providers\Modules;

class Issues {

    const KEY_ID                = 'id';
    const KEY_ISSUE_ID          = 'issue_id';
    const KEY_NAME              = 'name';
    const KEY_ICON              = 'icon';
    const KEY_INFORMATION       = "information";
    const KEY_DATE              = "date";

    const ALL_ISSUES = [
        [
            self::KEY_ID          => 1,
            self::KEY_NAME        => 'Tax document',
            self::KEY_INFORMATION => 'Get the tax document for yearly summary',
        ],
        [
            self::KEY_ID          => 2,
            self::KEY_NAME        => 'Lost package',
            self::KEY_INFORMATION => 'Ordered package via DHL - they have lost it',
        ],
        [
            self::KEY_ID          => 3,
            self::KEY_NAME        => 'Missing payment',
            self::KEY_INFORMATION => 'There is one payment missing from side-job',
        ],
    ];

    const ALL_ISSUES_PROGRESS = [
        [
            self::KEY_ISSUE_ID    => 1,
            self::KEY_INFORMATION => "I've sent them a message via government website - waiting for response",
            self::KEY_DATE        => "2020-02-24 09:00:00",

        ],
        [
            self::KEY_ISSUE_ID    => 2,
            self::KEY_INFORMATION => "Messaged via website and contacted customer center",
            self::KEY_DATE        => "2020-01-06 09:00:00",
        ],
        [
            self::KEY_ISSUE_ID    => 2,
            self::KEY_INFORMATION => "They've sent me a letter in which they say that they will do all they can do find my package - and that's just it",
            self::KEY_DATE        => "2020-02-06 16:00:00",
        ],
        [
            self::KEY_ISSUE_ID    => 3,
            self::KEY_INFORMATION => "Visited them personally",
            self::KEY_DATE        => "2020-03-06 16:32:00",
        ],
    ];

    const ALL_ISSUES_CONTACTS = [
        [
            self::KEY_ISSUE_ID    => 1,
            self::KEY_INFORMATION => "Entire documentation was sent alongside with the attachment (pdf)",
            self::KEY_ICON        => "far fa-envelope-open",
            self::KEY_DATE        => "2020-02-24 08:49:04",
        ],
        [
            self::KEY_ISSUE_ID    => 2,
            self::KEY_INFORMATION => "Message via contact form on their website https://dhl.de",
            self::KEY_ICON        => "fas fa-money-check",
            self::KEY_DATE        => "2020-01-06 08:00:00",
        ],
        [
            self::KEY_ISSUE_ID    => 2,
            self::KEY_INFORMATION => "Contacted customer center - VERY unpleasant person",
            self::KEY_ICON        => "fas fa-phone",
            self::KEY_DATE        => "2020-01-06 08:30:00",
        ],
        [
            self::KEY_ISSUE_ID    => 3,
            self::KEY_INFORMATION => "Got my money - they had some financial internal problems",
            self::KEY_ICON        => "fas fa-dollar-sign",
            self::KEY_DATE        => "2020-03-06 17:30:00",
        ],
    ];

}