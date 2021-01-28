<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Api;
use \WP_List_Table;

class OCSingleEpisodeListTable extends \WP_List_Table
{
    private $single_episodes = array();
    private $data_columns = array();
    private $data_hidden_columns = array();
    private $data_sortable_columns = array();
    private $data_actions = array();
    private $opencast_options = array();

    function __construct( $single_episodes, $columns, $hidden_column, $sortable_columns, $actions) {
        $this->opencast_options = get_option(OPENCAST_OPTIONS);
        $this->single_episodes = $single_episodes;
        $this->data_columns = $columns;
        $this->data_hidden_columns = $hidden_column;
        $this->data_sortable_columns = $sortable_columns;
        $this->data_actions = $actions;
        parent::__construct( [
			'singular' => __( 'Single Episode', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Single Episodes', 'sp' ), //plural name of the listed records
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
        $hidden = array();
        $sortable = $this->data_sortable_columns;
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $this->single_episodes;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) { 
            case 'name':
            case 'oc_id':
            case 'class':
            case 'usepermissions':
            case 'permissions':
            case 'actions':
                return ((is_array($item[ $column_name ])) ? implode(', ', $item[ $column_name ]) : $item[ $column_name ]);
            default:
                return __('No value Found!'); 
        }
    }

    function column_cb($item) {
        if (!empty($this->data_actions)) {
            return sprintf(
                '<input type="checkbox" class="oc-cb-se-select" data-id="%s" />', $item['name']
            );
        }
        return '';
    }

    function get_bulk_actions() {
        $actions = array();
        if (!empty($this->data_actions)) {
            foreach (array_keys($this->data_actions) as $action_name) {
                if ($action_name == 'delete') {
                    $actions[$action_name] = __(ucfirst($action_name));
                }
            }
        }
        return $actions;
    }

    /* function search_box($name, $id) {
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
    } */
}