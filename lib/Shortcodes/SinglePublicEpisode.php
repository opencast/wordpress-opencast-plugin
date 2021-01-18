<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Shortcodes;

class SinglePublicEpisode
{

     /**
     * Class registeration.
     */
    public function register()
    {
        if (!get_option(OPENCAST_OPTIONS)) {
            return;
        }
        add_shortcode('opencast-episode-single-public', [$this, 'generate_opencast_single_episode_public']);
    }

    public function generate_opencast_single_episode_public($attr)
    {
        $class = (isset($attr['class']) && !empty($attr['class'])) ? esc_html($attr['class']) : '';
        $oc_id = (isset($attr['oc_id']) && !empty($attr['oc_id'])) ? esc_html($attr['oc_id']) : '';
        if ($oc_id) {
            $single_episode_container = "<style>div.oc-player-container iframe.oc-player{width:95%;height:455px}</style>";
            $single_episode_container .= "<div class='oc-player-container {$class}'>";
            $opencast_options = get_option(OPENCAST_OPTIONS);
            $endpoint = ((isset($opencast_options['episodeendpoiturl']) && !empty($opencast_options['episodeendpoiturl'])) ? $opencast_options['episodeendpoiturl'] : $opencast_options['apiurl']);
            $src = rtrim($endpoint, '/') . "/paella/ui/embed.html?id=$oc_id";
            $single_episode_container .= "<iframe src='$src' id='$oc_id' class='oc-player' allowfullscreen='true'></iframe>";
            $single_episode_container .= "</div>";
            return $single_episode_container;
        }
        return '';
    }

}

?>