<?php

namespace App\Enum;

/**
 * @description these are referenced also on frontend, so whenever right name is changed it has to be updated on the front too.
 */
enum UserModuleRightEnum
{
    case CAN_ACCESS_GOALS_MODULE;
    case CAN_ACCESS_TODO_MODULE;
    case CAN_ACCESS_NOTES_MODULE;
    case CAN_ACCESS_CONTACTS_MODULE;
    case CAN_ACCESS_PASSWORDS_MODULE;
    case CAN_ACCESS_ACHIEVEMENTS_MODULE;
    case CAN_ACCESS_CALENDAR_MODULE;
    case CAN_ACCESS_ISSUES_MODULE;
    case CAN_ACCESS_TRAVELS_MODULE;
    case CAN_ACCESS_PAYMENTS_MODULE;
    case CAN_ACCESS_SHOPPING_MODULE;
    case CAN_ACCESS_JOB_MODULE;
    case CAN_ACCESS_REPORTS_MODULE;
    case CAN_ACCESS_FILES_MODULE;
    case CAN_ACCESS_IMAGES_MODULE;
    case CAN_ACCESS_VIDEOS_MODULE;
    case CAN_ACCESS_STORAGE_MODULE;
}
