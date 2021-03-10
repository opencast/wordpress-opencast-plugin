<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Shortcodes;

class OCSinglePublicEpisode extends OCShortcodeController
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
        $class = (!empty($attr['class']) ?  implode(' ', array_map('sanitize_text_field', explode(' ', $attr['class']))) : '');
        $oc_id = sanitize_key((isset($attr['oc_id']) && !empty($attr['oc_id'])) ? $attr['oc_id'] : '');
        $oc_url = esc_url_raw((isset($attr['oc_url']) && !empty($attr['oc_url'])) ? $attr['oc_url'] : '');
        if ($oc_id || $oc_url) {

            $default_css = "div.oc-player-container iframe.oc-player{width:95%;height:455px}";
            $defaul_style_name = 'oc-single-episode-public-style';
            $this->opencast_add_inline_style($defaul_style_name, $default_css);

            $single_episode_container = "";
            $single_episode_container .= "<div class='oc-player-container " . esc_attr($class) . "'>";

            if ($oc_id) {
                $opencast_options = get_option(OPENCAST_OPTIONS);
                $endpoint = ((isset($opencast_options['episodeendpoiturl']) && !empty($opencast_options['episodeendpoiturl'])) ? $opencast_options['episodeendpoiturl'] : $opencast_options['apiurl']);
                $oc_id = esc_attr($oc_id);
                $src = esc_attr(rtrim($endpoint, '/') . "/paella/ui/embed.html?id=$oc_id");
            }
            if ($oc_url) {
                $src = $oc_url;
            }
            $single_episode_container .= "<iframe src='$src' id='$oc_id' class='oc-player' allowfullscreen='true'></iframe>";
            $single_episode_container .= "</div>";
            return $single_episode_container;
        }
        return '';
    }

}

?>