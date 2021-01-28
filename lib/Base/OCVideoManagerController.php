<?php 
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Base;
use Opencast\Api\OCRestAPI;
use Opencast\Api\OCVideoListTable;


class OCVideoManagerController
{
    public function register()
    {
        foreach (get_class_methods($this) as $function) {
            if (strpos($function, '_ajax') !== FALSE) {
                add_action("wp_ajax_{$function}", [$this, $function]);
            }
        }
    }

    public function oc_admin_video_manager_index() 
    {
        $video_per_page = $this->prepare_video_per_page();
        $video_list_table = new OCVideoListTable(
                                    $this->get_videos(),
                                    $this->set_columns(), 
                                    $this->set_hidden_columns(),
                                    $this->set_sotable_columns(), 
                                    $this->get_actions(), 
                                    $video_per_page);

        echo "<div class='wrap oc-admin-wrapper oc-admin-video-list' data-ajaxurl='" . admin_url('admin-ajax.php') . "'>"
                            ."<h1>Opencast Plugin</h1>";                    
        echo $video_list_table->limit_box();
        echo $video_list_table->search_box('Search', 'search_id');        
        $video_list_table->prepare_items();
        $video_list_table->display();
        echo "</div>";
    }

    private function get_videos() 
    {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $response = array();
        $request = new OCRestAPI();
        $series_id = ((isset($opencast_options['episodeseriesid']) && !empty($opencast_options['episodeseriesid'])) ? $opencast_options['episodeseriesid'] : $opencast_options['seriesid']);

        $sortable = array(
            'orderby' => ((isset($_GET['orderby']) && !empty($_GET['orderby'])) ? sanitize_key($_GET['orderby']) : 'start_date'),
            'order' => ((isset($_GET['order']) && !empty($_GET['order'])) ? strtoupper(sanitize_key($_GET['order'])) : 'ASC'),
        );
        $sort_string = "sort={$sortable['orderby']}:{$sortable['order']}";

        
        $filters = array(
            urlencode("is_part_of:$series_id"),
        );

        //default php sanitization (no proper sanitize_* method found)
        $oc_search = isset($_GET['oc_search']) ? filter_var(urldecode($_GET['oc_search']), FILTER_SANITIZE_STRING) : '';
        if ($oc_search) {
            $dt = \DateTime::createFromFormat(get_option('date_format') . ' ' . get_option('time_format'), $oc_search);
            if ($dt) {
                $ts = $dt->getTimestamp();  
                $start = date('Y-m-d',$ts) . 'T' . date('H:i:s',$ts) . 'Z';
                $filters[] = urlencode("start:" . urlencode($start . '/' . $start));
            } else {
                $filters[] = urlencode("title:$oc_search");
            }
        }

        if ($search_result = $request->oc_get("/api/events?filter=" . implode(',', $filters) . "&$sort_string")) {
            //adding actions
            foreach ( $search_result as $video_obj ) {
                //actions 
                $actions = array();
                foreach ($this->get_actions() as $action_name => $action_template) {
                    if ($action_name == 'delete') {
                        $delete_id = $video_obj['identifier'];
                        $actions[] = sprintf($action_template, $delete_id);
                    }
                }
                $video_obj['actions'] = implode(' &nbsp ', $actions);
                $response[] = $video_obj;
            }
        }
        return $response;
    }

    private function set_hidden_columns() {
        $hidden_columns = array(
        );
        return $hidden_columns;
    }

    private function set_columns() {
        $columns = array();
        if (!empty($this->get_actions())) {
            $columns['cb'] ='<input type="checkbox" />';
        }
        $columns = $columns + array(
            'title'               => __('Title'),
            'presenter'           => __('Presenter(s)'),
            'start'               => __('Date'),
            'location'            => __('Location'),
            'publication_status'  => __('Published'),
            'status'              => __('Status'),
        );
        if (!empty($this->get_actions())) {
            $columns['actions'] = __('Actions');
        }
        return $columns;
    }

    private function set_sotable_columns() {
        $sortable_columns = array(
            'title'  => array('title',false),
            'presenter' => array('presenter',false),
            'start'   => array('start_date',false),
            'location'   => array('location',false),
        );
        return $sortable_columns;
    }

    private function get_actions() {
        $actions = array(
            'delete' => "<a href='#' class='oc-admin-video-delete' data-id='%s'><span class='dashicons dashicons-trash'></span></a>"
        );
        return $actions;
    }

    private function prepare_video_per_page() {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $user = wp_get_current_user();
        $video_per_page = 10;
        if (isset($opencast_options['video_manager_per_page'][$user->ID])) {
            $video_per_page = $opencast_options['video_manager_per_page'][$user->ID];
        }
        return $video_per_page;
    }

    public function save_limit_ajax() 
    {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $user = wp_get_current_user();
        $video_per_page = sanitize_key(absint($_POST['oc_table_limit']));
        if (!$video_per_page) {
            $video_per_page = 10;
        }
        $opencast_options['video_manager_per_page'][$user->ID] = $video_per_page;
        update_option( OPENCAST_OPTIONS, $opencast_options );
        $response = array(
            'success' => true
        );
        wp_send_json($response);
        wp_die();
    }

    public function delete_videos_ajax() 
    {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $response = array();
        $request = new OCRestAPI();
        $endpoint = ((isset($opencast_options['episodeendpoiturl']) && !empty($opencast_options['episodeendpoiturl'])) ? rtrim($opencast_options['episodeendpoiturl'], '/') : '');
        if ($endpoint) {
            $request->setUrl($endpoint);
        }
        $deleted_vidoes = array();
        $videos_to_delete = isset( $_POST['videos'] ) ? (array) $_POST['videos'] : array();
        //sanitizing array
        $videos_to_delete = array_map( 'sanitize_key', $videos_to_delete );
        if (is_array($videos_to_delete) && !empty($videos_to_delete)) {
            foreach ($videos_to_delete as $video_id) {
                if ($video_id) {
                    if ($delete = $request->oc_delete("/api/events/$video_id")) {
                        $deleted_vidoes[] = $video_id;
                    }
                }
            }
            if (!empty($not_deleted = array_diff($videos_to_delete, $deleted_vidoes))) {
                if (count($not_deleted) == $videos_to_delete) {
                    $response['error'] = __('Unable to delete videos');
                } else {
                    $response['success']['notdeleted']  = $not_deleted;
                    $response['success']['deleted']     = $deleted_vidoes;
                }
            } else {
                $response['success']['deleted'] = $deleted_vidoes;
            }
        } else {
            $response['error'] = __('No Video Lists');
        }
        wp_send_json($response);
        wp_die();
    }
    
}

?>