<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Shortcodes;

use Opencast\Api\OCRestAPI;
use Opencast\Api\LTIConsumer;

class Episodes
{
     /**
     * Class registeration.
     */
    public function register()
    {
        if (!get_option(OPENCAST_OPTIONS)) {
            return;
        }
        add_shortcode('opencast-episodes', [$this, 'generate_opencast_episodes']);
    }

    public function generate_opencast_episodes($attr)
    {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        if (is_array($episodes = $this->get_episodes($opencast_options))) {
            $attributes = array(
                'id' => (isset($attr['id']) && !empty($attr['id'])) ? esc_html($attr['id']) : 'oc-episodes',
                'name' => (isset($attr['name']) && !empty($attr['name'])) ? esc_html($attr['name']) : 'oc-episodes',
                'class' => (isset($attr['class']) && !empty($attr['class'])) ? esc_html($attr['class']) : 'opencast-episodes-container'
            );
            if (isset($episodes['list']) && $episodes['list']) {
                $rendered_episodes = '';
                $limit = $episodes['limit'];
                $total = $episodes['total'];
                if ($limit && $limit < $total) {
                    $rendered_episodes .= $this->generate_default_style_pagination();
                }

                wp_enqueue_script( 'paginationjs', PLUGIN_DIR_URL . 'src/vendors/pagination/pagination.js', array('jquery'), '2.1.5' );
                wp_enqueue_script( 'sweetalert2', PLUGIN_DIR_URL . 'src/vendors/sweetalert2/sweetalert2.js', array('jquery'), '9.15.2' );
                wp_enqueue_script( 'episodes.js', PLUGIN_DIR_URL . 'src/js/inlines/episodes.js', array('jquery', 'paginationjs', 'sweetalert2'), PLUGIN_VERSION );

                $rendered_episodes .= $this->render_episodes($attributes, $episodes, $opencast_options);
                return $rendered_episodes;
            } else {
                return $this->render_empty();
            }
            
        }
        return false;
    }

    private function get_episodes($opencast_options) {
        
        $episodeusepermissions = (isset($opencast_options['episodeusepermissions'])) ? $opencast_options['episodeusepermissions'] : true;
        if ($episodeusepermissions) {
            $episodepermission = (isset($opencast_options['episodepermission']) && $opencast_options['episodepermission']) ? 
                                        ((!is_array($opencast_options['episodepermission'])) ? array($opencast_options['episodepermission']) : $opencast_options['episodepermission'])
                                        : array();
            if ($episodepermission && is_user_logged_in()) {
                $user = wp_get_current_user();
                $user_roles = ( array ) $user->roles;
                if (empty(array_intersect($user_roles,$episodepermission))) {
                    return false;
                }    
            } else {
                return false;
            }
        }

        $limit = (isset($opencast_options['episodepagelimit']) && !empty(trim($opencast_options['episodepagelimit']))) ? $opencast_options['episodepagelimit'] : 0;
        $sort = (isset($_GET['oc_episode_sort'])) ? $_GET['oc_episode_sort'] : '';
        
        $request = new OCRestAPI();
        $series_id = ((isset($opencast_options['episodeseriesid']) && !empty($opencast_options['episodeseriesid'])) ? $opencast_options['episodeseriesid'] : $opencast_options['seriesid']);
        $endpoint = ((isset($opencast_options['episodeendpoiturl']) && !empty($opencast_options['episodeendpoiturl'])) ? $opencast_options['episodeendpoiturl'] : $opencast_options['apiurl']);
        if ($endpoint) {
            $request->setUrl($endpoint);
        }
        
        if ($search_result = $request->oc_get("/search/episode.json?sid=$series_id&sort=$sort")) {
            $episodes_list = array();
            if (!isset($search_result['search-results']['result'])) {
                return array();
            }
            
            $episodes_list['list'] = $search_result['search-results']['result'];

            if (isset($episodes_list['list']['id'])) {
                $episodes_list['list'] = array($episodes_list['list']);
            }

            $episodes_list['total'] = $search_result['search-results']['total'];
            $episodes_list['limit'] = $limit;

            return $episodes_list;
        }
        return false;
    }

    private function render_episodes($attributes, $episodes, $opencast_options){
        $episodes_container = '';
        if (isset($attributes['class']) && strpos($attributes['class'], 'opencast-episodes-container') !== FALSE) {
            $episodes_container = $this->generate_default_style_episodes();
        }

        $total = $episodes['total'];
        $limit = $episodes['limit'];
        $episodes_list = $episodes['list'];
        $endpoint = ((isset($opencast_options['episodeendpoiturl']) && !empty($opencast_options['episodeendpoiturl'])) ? $opencast_options['episodeendpoiturl'] : $opencast_options['apiurl']);
        
        $episodes_container .= "<div data-total='$total' data-limit='$limit' id='{$attributes['id']}' name='{$attributes['name']}' class='{$attributes['class']}'>";       
        foreach ($episodes_list as $episode) {
            $player_src = rtrim($endpoint, '/') . '/paella/ui/embed.html?id=' . $episode['id'];
            $preview_src = $this->get_preview_src($episode['mediapackage']);
            $title = $this->get_title($episode['mediapackage']);
            $creator = $this->get_creator($episode['mediapackage']);
            $date_time = $this->get_date_time($episode['mediapackage']);
            $episodes_container .=   "<a data-playersrc='$player_src' class='episode' href='#'>"
                            ."<div class='preview'>"
                                ."<img alt='Preview' src='$preview_src'>"
                            ."</div>"
                            ."&nbsp"
                            ."<div class='desc'>"
                                ."<h2>{$title}</h2>"
                                .__($creator)
                                ."<br>"
                                .$date_time
                            ."</div>"
                        ."</a>";
        }
        $episodes_container .= $this->render_player_lti_form($opencast_options);
        $episodes_container .= "</div>";
        return $episodes_container;
    }

    private function render_player_lti_form($opencast_options){

        $consumerkey = (isset($opencast_options['consumerkey']) ? $opencast_options['consumerkey'] : '');
        $consumersecret = (isset($opencast_options['consumersecret']) ? $opencast_options['consumersecret'] : '');
        if (!$consumerkey && !$consumersecret) {
            return '';
        }

        $endpoint = rtrim($opencast_options['apiurl'], '/') . '/lti';
        $customtools = 'ltitools';
        return LTIConsumer::lti_launch($endpoint, $consumerkey, $consumersecret, $customtools, false);

    }

    private function render_empty() {
        return __('Unable to find Videos!');
    }

    private function get_player_src($mediapackage) {
        $player_src = '';
        $quality = 0;
        $media_resource_tracks = $mediapackage['media']['track'];
        foreach ($media_resource_tracks as $track) {
            $tag_quality = str_replace('-quality', '', $track['tags']['tag'][0]);
            if ($tag_quality > $quality) {
                $player_src = $track['url'];
            }
        }
        return $player_src;
    }

    private function get_preview_src($mediapackage) {
        $attachments = array_column($mediapackage['attachments']['attachment'], 'url', 'type');
        $preview_src = '';
        if (array_key_exists("presentation/search+preview", $attachments)) {
            $preview_src = $attachments["presentation/search+preview"];
        } elseif (array_key_exists("presentation/player+preview", $attachments)) {
            $preview_src = $attachments["presentation/player+preview"];
        } elseif (array_key_exists("presenter/search+preview", $attachments)) {
            $preview_src = $attachments["presenter/search+preview"];
        } elseif (array_key_exists("presenter/player+preview", $attachments)) {
            $preview_src = $attachments["presenter/player+preview"];
        }
        return $preview_src;
    }

    private function get_title($mediapackage) {
        $title = (isset($mediapackage['title']) ? $mediapackage['title'] : '');
        return $title;
    }

    private function get_creator($mediapackage) {
        $creator = (isset($mediapackage['creators']) ? $mediapackage['creators']['creator'] : '');
        return ((is_array($creator)) ? implode(', ', $creator) : $creator);
    }

    private function get_date_time($mediapackage) {
        $dt_gmt = $mediapackage['start'];
        $dt = get_date_from_gmt($dt_gmt, 'd.m.Y H:i:s');
        $date_time =  date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($dt));
        return $date_time;
    }

    private function generate_default_style_episodes() {
        return "<style>".
                    "div.opencast-episodes-container a.episode{display:block;text-decoration:none;color:#000;background-color:#eee;padding:5px;margin:15px}div.opencast-episodes-container a.episode div{vertical-align:middle;display:inline-block;padding:20px}div.opencast-episodes-container a.episode div.preview{width:200px}div.opencast-episodes-container a.episode div.preview img{max-width:160px!important;}div.opencast-episodes-container a.episode div.desc h2{margin:0 0 5px}div.opencast-episodes-container a.episode:hover{background-color:#fafafa}".
                    file_get_contents(PLUGIN_DIR . 'src/vendors/sweetalert2/sweetalert2.css').
                "</style>";
    }

    private function generate_default_style_pagination() {
        return "<style>".
                    file_get_contents(PLUGIN_DIR . 'src/vendors/pagination/pagination.css').
                "</style>";
    }
}

?>