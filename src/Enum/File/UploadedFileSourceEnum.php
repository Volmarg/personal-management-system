<?php

namespace App\Enum\File;

/**
 * Describes the allowed file sources.
 * Where source means where was it uploaded / what it will be used for
 */
enum UploadedFileSourceEnum: string
{
    case PROFILE_IMAGE = "PROFILE_IMAGE";
    case STORAGE_IMAGE_MODULE = "STORAGE_IMAGE_MODULE";
    case STORAGE_FILE_MODUlE = "STORAGE_FILE_MODUlE";
    case STORAGE_VIDEO_MODULE = "STORAGE_VIDEO_MODULE";
}
