<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Shortcodes;

use Opencast\Api\OCRestAPI;
use Opencast\Api\OCLTIConsumer;

class OCSingleEpisode extends OCShortcodeController
{
    private $oc_id;
    private $wp_id;
    private $class;
    private $usepermissions;
    private $permissions;
    private $opencast_options = array();
    private $single_episode_list = array();

     /**
     * Class registeration.
     */
    public function register()
    {
        if (!get_option(OPENCAST_OPTIONS)) {
            return;
        }
        add_shortcode('opencast-episode-single', [$this, 'generate_opencast_single_episode']);
    }

    public function generate_opencast_single_episode($attr)
    {
        $this->oc_id = '';
        $this->wp_id = '';
        $this->class = '';
        $this->usepermissions = true;
        $this->permissions = array();


        $this->opencast_options = get_option(OPENCAST_OPTIONS);
        $this->single_episode_list = ((isset($this->opencast_options['singleepisodelist'])) ? $this->opencast_options['singleepisodelist'] : array());
       
        if ($this->set_single_episode_object($attr)) {

            if ($this->usepermissions) {
                if ($this->permissions && is_user_logged_in()) {
                    $user = wp_get_current_user();
                    $user_roles = ( array ) $user->roles;
                    if (empty(array_intersect($user_roles, $this->permissions))) {
                        return false;
                    }    
                } else {
                    return false;
                }
            }

            $single_episode_container = '';
            if ($this->oc_id) {
                $single_episode_container .= $this->render_single_episode();
            } else {
                $single_episode_container .= $this->render_selectable_episodes_list();
            }
            return $single_episode_container;
        }
        return __('Error: An Unique ID must be presented!');
    }

    private function set_single_episode_object($attr) {

        $user = wp_get_current_user();
        $user_roles = ( array ) $user->roles;

        //Shortcode Attributes
        $this->wp_id = sanitize_key((isset($attr['wp_id']) && !empty($attr['wp_id'])) ? $attr['wp_id'] : '');
        $this->class = (!empty($attr['class']) ?  implode(' ', array_map('sanitize_text_field', explode(' ', $attr['class']))) : '');

        $single_episode_list = $this->single_episode_list;

        if (!$this->wp_id) {
            return false;
        }

        if (isset($single_episode_list[$this->wp_id])) {
            $episode_data = $single_episode_list[$this->wp_id];
            $this->oc_id = $episode_data['oc_id'];
            $this->usepermissions = $episode_data['usepermissions'];
            $this->permissions  = $episode_data['permissions'];
            if (isset($_GET['oc_id']) && empty(trim($this->oc_id)) && isset($_GET['wp_id']) && $this->wp_id == sanitize_key($_GET['wp_id'])) {
                $this->oc_id = sanitize_key($_GET['oc_id']);
                $this->usepermissions = true;
                $this->permissions = array_unique(array_merge($user_roles, $this->permissions));
                $this->store_single_episode_options();
            }

        } else {
            $this->permissions = $user_roles;
            $this->store_single_episode_options();
        }

        
        return true;
    }

    private function store_single_episode_options(){ 
        $opencast_options = $this->opencast_options;
        $single_episode_list = $this->single_episode_list;

        $single_episode_list[$this->wp_id] = array(
            "oc_id" => $this->oc_id,
            "class" => $this->class,
            "usepermissions" => $this->usepermissions,
            "permissions" => $this->permissions,
        );

        $opencast_options['singleepisodelist'] = $single_episode_list;
        $action = sanitize_key((isset($_POST['action']) ? $_POST['action'] : ''));
        if ($action != 'elementor_ajax') { 
            update_option( OPENCAST_OPTIONS, $opencast_options );
        }
    }

    private function render_selectable_episodes_list(){
        
        global $wp;
        $current_page_url = (get_permalink()) ? get_permalink() : home_url( $wp->request );
        $current_page_query_vars = $wp->query_vars;

        $class = array('oc-selectable-list');
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_script( 'single-episode.js', OPENCAST_PLUGIN_DIR_URL . 'src/js/inlines/single-episode.js', array('jquery'), OPENCAST_PLUGIN_VERSION );
        
        $selectable_css = $this->generate_default_style();
        $selectable_style_name = 'oc-single-episode-selectable-style';
        $this->opencast_add_inline_style($selectable_style_name, $selectable_css);

        $selectable_episodes_list = '';
        
        if ($episodes = $this->get_episodes()) {
            $videos = array();
            foreach ($episodes as $episode) {
                $preview_src = esc_attr($this->get_preview_src($episode['mediapackage']));
                $title = esc_html($this->get_title($episode['mediapackage']));
                $creator = esc_html($this->get_creator($episode['mediapackage']));
                $date_time = esc_html($this->get_date_time($episode['mediapackage']));
                $oc_id = $episode['mediapackage']['id'];
                $current_page_query_vars['oc_id'] = $oc_id;
                $current_page_query_vars['wp_id'] = $this->wp_id;
                $select_link = esc_attr(add_query_arg($current_page_query_vars , $current_page_url ));
                $videos[] = "<div class='episode oc-select-redirect' data-selectlink='$select_link'>"
                                    ."<div class='background' style='background: url(\"$preview_src\") no-repeat;;background-size:100% 100%;'><img src='$preview_src' style='visibility: hidden;'></div>"
                                    ."<div class='overlay' style=''></div>"
                                    ."<div class='content' style=''>"
                                        ."<p class='title'>{$title}</p>"
                                        ."<small class='creator'>".__($creator)."</small>"
                                        ."<small class='date'>{$date_time}</small>"
                                    ."</div>"
                                ."</div>";
            }

            $rows = array();
            $rows_counter = 0;
            for ($v = 0; $v < count($videos); $v++) {
                if ($rows && count($rows[$rows_counter]) == 2) {
                    $rows_counter++;
                }
                $rows[$rows_counter][] = $videos[$v];
            }

            $offsets = array();
            $offsets_counter = 0;
            for ($r = 0; $r < count($rows); $r++) {
                if ($offsets && count($offsets[$offsets_counter]) == 2) {
                    $offsets_counter++;
                }
                $offsets[$offsets_counter][] = $rows[$r];
            }

            (count($offsets) > 1) ? $class[] = 'oc-searchable-list' : '';
            $selectable_episodes_list .= "<div class='" . esc_attr(implode(' ', $class)) . "'>";

            $selectable_episodes_list .= "<div class='oc-list-direction oc-prev' style='display: none;'><a class='previous' style='' href='#'>&#8249;</a></div>";
            $selectable_episodes_list .= "<div class='oc-list-direction oc-next' style='display: none;'><a class='next' style='' href='#'>&#8250;</a></div>";
            $selectable_episodes_list .= "<div class='search' style='display: none;'><input type='text' placeholder='" . esc_html(__('Search')) . "' class='oc-list-search-text'/></div>";

            foreach ($offsets as $index => $offset) {
                $selectable_episodes_list .= "<div class='offset " . (($index == 0) ? 'active' : '') . "' data-index='" . esc_attr(($index + 1)) . "'>";

                foreach ($offset as $rows) {
                    $selectable_episodes_list .= "<div class='row'>" . implode('', $rows) . "</div>"; //esc_html must not apply here since $rows contains div of videos
                }
                $selectable_episodes_list .= "</div>";
            }

            $selectable_episodes_list .= "</div>";
            return $selectable_episodes_list;
        }
        return '';
    }

    private function render_single_episode(){
        
        wp_enqueue_script( 'sweetalert2', OPENCAST_PLUGIN_DIR_URL . 'src/vendors/sweetalert2/sweetalert2.js', array('jquery'), '9.15.2' );
        wp_enqueue_script( 'single-episode.js', OPENCAST_PLUGIN_DIR_URL . 'src/js/inlines/single-episode.js', array('jquery', 'sweetalert2'), OPENCAST_PLUGIN_VERSION );

        $default_css = "div.oc-player-container iframe.oc-player{width:95%;height:455px}";
        $defaul_style_name = 'oc-single-episode-style';
        $this->opencast_add_inline_style($defaul_style_name, $default_css);

        $single_episode_container = "<div class='oc-player-container " . esc_attr($this->class) . "'>";
        $opencast_options = $this->opencast_options;
        $endpoint = ((isset($opencast_options['episodeendpoiturl']) && !empty($opencast_options['episodeendpoiturl'])) ? $opencast_options['episodeendpoiturl'] : $opencast_options['apiurl']);
        $src = esc_attr(rtrim($endpoint, '/') . '/paella/ui/embed.html?id=' . $this->oc_id);
        $safarihelper = esc_attr(OPENCAST_PLUGIN_DIR_URL . 'safariHelper.php');
        if ($this->usepermissions) {
            $consumerkey = (isset($opencast_options['consumerkey']) ? $opencast_options['consumerkey'] : '');
            $consumersecret = (isset($opencast_options['consumersecret']) ? $opencast_options['consumersecret'] : '');
            if (!$consumerkey && !$consumersecret) {
                return false;
            }
            
            $endpoint_lti = rtrim($endpoint, '/') . '/lti';
            $customtools = 'ltitools';
            $single_episode_container .= OCLTIConsumer::lti_launch($endpoint_lti, $consumerkey, $consumersecret, $customtools, false);
            
            $single_episode_container .= "<iframe data-playersrc='$src' data-safarihelper='$safarihelper' src='' class='oc-player' allowfullscreen='true'></iframe>";
        } else {
            $single_episode_container .= "<iframe src='$src' class='oc-player' allowfullscreen='true'></iframe>";
        }
        $single_episode_container .= "</div>";
        return $single_episode_container;
    }

    private function get_episodes() {

        $opencast_options = $this->opencast_options;

        $request = new OCRestAPI();
        $series_id = ((isset($opencast_options['episodeseriesid']) && !empty($opencast_options['episodeseriesid'])) ? $opencast_options['episodeseriesid'] : $opencast_options['seriesid']);
        $endpoint = ((isset($opencast_options['episodeendpoiturl']) && !empty($opencast_options['episodeendpoiturl'])) ? $opencast_options['episodeendpoiturl'] : $opencast_options['apiurl']);
        if ($endpoint) {
            $request->opencast_set_url($endpoint);
        }
        if ($search_result = $request->opencast_get("/search/episode.json?sid=$series_id")) {
            $episodes_list = array();
            if (!isset($search_result['search-results']['result'])) {
                return array();
            }
            
            $episodes_list = $search_result['search-results']['result'];

            if (isset($episodes_list['id'])) {
                $episodes_list = array($episodes_list);
            }
            return $episodes_list;
        }
        return false;
    }

    private function render_empty() {
        return __('Unable to find Videos!');
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
        return $creator;
    }

    private function get_date_time($mediapackage) {
        $dt_gmt = $mediapackage['start'];
        $dt = get_date_from_gmt($dt_gmt, 'd.m.Y H:i:s');
        $date_time =  date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($dt));
        return $date_time;
    }

    ## INFO:
    /*
     * constant OPENCAST_PLUGIN_DIR is using the proper file location with (__FILE__) please refer to ../../opencast-constants.php
     */
    private function generate_default_style() {
        return file_get_contents(OPENCAST_PLUGIN_DIR . 'src/css/inlines/single-episode.css');
    }
}

?>