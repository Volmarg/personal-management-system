<?php

namespace App\Enum\File;

/**
 * Must be kept in sync with front `UploadStatusMixin`
 */
enum UploadStatusEnum: string
{
    case SUCCESS = "success";
    case ERROR   = "error";
}
