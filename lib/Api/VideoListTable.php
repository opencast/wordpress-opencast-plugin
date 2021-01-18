<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Api;
use \WP_List_Table;

class VideoListTable extends \WP_List_Table
{
    private $videos = array();
    private $data_columns = array();
    private $data_hidden_columns = array();
    private $data_sortable_columns = array();
    private $data_actions = array();
    private $data_video_per_page;
    private $opencast_options = array();

    function __construct( $videos, $columns, $hidden_column, $sortable_columns, $actions , $video_per_page) {
        $this->opencast_options = get_option(OPENCAST_OPTIONS);
        $this->videos = $videos;
        $this->data_columns = $columns;
        $this->data_hidden_columns = $hidden_column;
        $this->data_sortable_columns = $sortable_columns;
        $this->data_video_per_page = $video_per_page;
        $this->data_actions = $actions;
        parent::__construct( [
			'singular' => __( 'Video List', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Video Lists', 'sp' ), //plural name of the listed records
			'ajax'     => true //should this table support ajax?
        ] );
    }

    function get_columns(){
        return $this->data_columns;
    }

    function get_hidden_columns(){
        return $this->data_hidden_columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array(
            'id' => 'identifier'
        );
        $sortable = $this->data_sortable_columns;
        $this->_column_headers = array($columns, $hidden, $sortable);

        //pagination
        $per_page = $this->get_items_per_page('oc_videos_per_page', $this->data_video_per_page);
        $current_page = $this->get_pagenum();
        $total_items = count($this->videos);
        $found_data = array_slice($this->videos,(($current_page-1)*$per_page),$per_page);
        $this->set_pagination_args( array(
            'total_items' => $total_items, 
            'per_page'    => $per_page 
            ) );
        $this->items = $found_data;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) { 
            case 'title':
            case 'presenter':
            case 'location':
            case 'publication_status':
            case 'actions':
                return ((is_array($item[ $column_name ])) ? implode(', ', $item[ $column_name ]) : $item[ $column_name ]);
            default:
                return __('No value Found!'); //Show the whole array for troubleshooting purposes
        }
    }

    function column_start ($item) {
        $dt = get_date_from_gmt($item['start'], 'd.m.Y H:i:s');
        $date_time =  date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($dt));
        return $date_time;
    }

    function column_status ($item) {
        $status = '';
        switch( $item['status'] ) { 
            case 'EVENTS.EVENTS.STATUS.PROCESSED':
                $status = __('Finished');
                if (count($item['publication_status']) == 1 && $item['publication_status'][0] == 'internal' && isset($item['has_previews']) && $item['has_previews']) {
                    $status = __('Needs Cutting');
                }
            break;
            case 'EVENTS.EVENTS.STATUS.SCHEDULED':
                $status = __('Planned');
            break;
            case 'EVENTS.EVENTS.STATUS.RECORDING':
                $status = __('Capturing');
            break;
            case 'EVENTS.EVENTS.STATUS.INGESTING':
            case 'EVENTS.EVENTS.STATUS.PENDING':
            case 'EVENTS.EVENTS.STATUS.PROCESSING':
                $status = __('Running');
            break;
            case 'EVENTS.EVENTS.STATUS.PROCESSING_FAILURE':
                $status = __('Failed');
            break;
            default: 
                $status = __('Undefined Status!');
        }
        return $status;
    }

    function column_cb($item) {
        if (!empty($this->data_actions)) {
            return sprintf(
                '<input type="checkbox" class="oc-cb-select" data-id="%s" />', $item['identifier']
            );
        }
        return '';
    }

    function get_bulk_actions() {
        $actions = array();
        if (!empty($this->data_actions)) {
            foreach (array_keys($this->data_actions) as $action_name) {
                $actions[$action_name] = __(ucfirst($action_name));
            }
        }
        return $actions;
    }

    function search_box($name, $id) {
        $oc_search = '';
        if (isset($_GET['oc_search']) && !empty(filter_var(urldecode($_GET['oc_search']), FILTER_SANITIZE_STRING))) {
            $oc_search = filter_var(urldecode($_GET['oc_search']), FILTER_SANITIZE_STRING);
        }
        $searchbox = '<p class="search-box">';
        $searchbox .= "<label class='screen-reader-text' for='$id-search-input'>" . __($name) . "</label>";
        $searchbox .= "<input id='$id-search-input' type='text' name='oc-table-search' value='$oc_search' />";
        $searchbox .= "<input id='search-submit' class='button' type='submit' name='s' value='" . __('Search') . "' />";
        if ($oc_search) {
            $searchbox .= "<a id='search-clear' style='text-decoration:none;color:red;cursor:pointer;'><span style='margin-top:5px;' class='dashicons dashicons-no-alt'></span></a>";
        }
        $searchbox .= '</p>';
        return $searchbox;
    }

    function limit_box() {
        $limitbox = '<p class="search-box limit-box" style="float:left!important;">';
        $limitbox .= "<label class='screen-reader-text' for=''>" . __('Limit Per Page') . "</label>";
        $limitbox .= "<input type='number' name='oc-table-limit' value='{$this->data_video_per_page}' />";
        $limitbox .= "<input id='limit-submit' class='button' type='submit' value='" . __('Apply Limit') . "' />";
        $limitbox .= '</p>';
        return $limitbox;
    }
}