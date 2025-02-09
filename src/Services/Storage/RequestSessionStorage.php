<?php

namespace App\Services\Storage;

/**
 * Session is not working with the frontend request, so this class is used to store data for current request instead
 */
class RequestSessionStorage
{
    public static bool $IS_SYSTEM_LOCKED = true;
    public static bool $IS_TOGGLE_LOCK_CALL = false;

}