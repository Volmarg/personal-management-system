<?php
/**
 * @description Was provided alongside with docker configuration, seems like this is something related to
 *              php7.4 preloading. Since there is a check for existing, and the preload is not configured,
 *              also was tested by other user, should not cause any particular problems
 */
if (file_exists(__DIR__.'/../var/cache/prod/srcApp_KernelProdContainer.preload.php')) {
    require __DIR__.'/../var/cache/prod/srcApp_KernelProdContainer.preload.php';
}
