<?php
/**
 * @package OpencastPlugin
 */

namespace Opencast;

final class OCInit
{
    /**
     * Store all the classes inside and array
     * @return array list of classes
     */
    public static function get_services()
    {
        return [
            Pages\OCAdmin::class,
            Base\OCEnqueue::class,
            Base\OCSettingLinks::class,
            Shortcodes\OCStudio::class,
            Shortcodes\OCEpisodes::class,
            Shortcodes\OCSingleEpisode::class,
            Shortcodes\OCSinglePublicEpisode::class,
            Shortcodes\OCUploadVideo::class,
            Base\OCVideoManagerController::class,
            Base\OCSingleEpisodeTableController::class,
        ];

    }

    /**
     * Loop through the classes and try to register the class
     * @return
     */
    public static function register_opencast_services()
    {
        foreach (self::get_services() as $class) {
            $service = new $class();
            if (method_exists($service, 'register')) {
                $service->register();
            }
        }
    }
}