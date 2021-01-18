<?php
/**
 * @package OpencastPlugin
 */

namespace Opencast;

final class Init
{
    /**
     * Store all the classes inside and array
     * @return array list of classes
     */
    public static function get_services()
    {
        return [
            Pages\Admin::class,
            Base\Enqueue::class,
            Base\SettingLinks::class,
            Shortcodes\Studio::class,
            Shortcodes\Episodes::class,
            Shortcodes\SingleEpisode::class,
            Shortcodes\SinglePublicEpisode::class,
            Shortcodes\UploadVideo::class,
            Base\VideoManagerController::class,
            Base\SingleEpisodeTableController::class,
        ];

    }

    /**
     * Loop through the classes and try to register the class
     * @return
     */
    public static function register_services()
    {
        foreach (self::get_services() as $class) {
            $service = new $class();
            if (method_exists($service, 'register')) {
                $service->register();
            }
        }
    }
}