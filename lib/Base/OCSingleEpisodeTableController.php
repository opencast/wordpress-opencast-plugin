<?php 
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Base;
use Opencast\Api\OCRestAPI;
use Opencast\Api\OCSingleEpisodeListTable;


class OCSingleEpisodeTableController
{
    private $activetabpane = "opencast_single_episode_option_section";
    public function register()
    {
        foreach (get_class_methods($this) as $function) {
            if (strpos($function, '_ajax') !== FALSE) {
                add_action("wp_ajax_{$function}", [$this, $function]);
            }
        }
    }

    public function generate_table($se_list, $wp_roles, $option_name) 
    {       
        $se_list_table = new OCSingleEpisodeListTable(
                                    $this->convert_list($se_list, $wp_roles, $option_name),
                                    $this->set_columns(), 
                                    $this->set_hidden_columns(),
                                    $this->set_sotable_columns(), 
                                    $this->get_actions());

        echo "<div class='oc-admin-se-list' data-ajaxurl='" . admin_url('admin-ajax.php') . "'>";
        echo "<input type='hidden' id='_wprls' value='" . base64_encode(json_encode($wp_roles)) . "'>";
        // echo $se_list_table->search_box('Search', 'search_id');
        $se_list_table->prepare_items();
        $se_list_table->display();
        echo "</div>";
    }

    private function convert_list($se_list, $wp_roles, $option_name)
    {
        $converted_list = array();
        foreach ($se_list as $name => $se_values) {
            $option_name_item = "{$option_name}[$name]";
            $single_episode_arr = array(
                'name' => $name,
                'oc_id' => $this->generate_option_column($se_values['oc_id'], 'oc_id', $option_name_item, $name),
                'class' => $this->generate_option_column($se_values['class'], 'class', $option_name_item, $name),
                'usepermissions' => $this->generate_option_column($se_values['usepermissions'], 'usepermissions', $option_name_item, $name)
            );
            $permissions = array();
            if (is_array($se_values['permissions'])) { 
                foreach ($se_values['permissions'] as $role_value) {
                    if (array_key_exists($role_value, $wp_roles)) {
                        $permissions[] = $wp_roles[$role_value];
                    }
                }
            }
            $value_str = ((!empty($permissions)) ? implode(', ', $permissions) : null);
            $single_episode_arr['permissions'] = $this->generate_option_column($se_values['permissions'], 'permissions', $option_name_item, $name, $value_str);
            $actions = array();
            foreach ($this->get_actions() as $action_name => $action_template) {
                if ($action_name == 'delete') {
                    $delete_id = $name;
                    $actions[] = sprintf($action_template, $delete_id);
                }
                if ($action_name == 'edit') {
                    $edit_id = $name;
                    $actions[] = sprintf($action_template, $edit_id);
                }
            }
            $single_episode_arr['actions'] = implode(' &nbsp ', $actions);
            $converted_list[] = $single_episode_arr;
        }

        return $converted_list;
    }

    private function generate_option_column($value, $key, $option_name, $id, $value_str = null){
        if (!$value_str) {
            $value_str = $value;
            if (is_bool($value)) {
                $value_str = (($value) ? '<span style="color:green" class="dashicons dashicons-yes-alt"></span>' : '<span style="color:red" class="dashicons dashicons-dismiss"></span>');
            }
            if (is_array($value)) {
                $value_str = implode(', ', $value);
            }
        }
        $hidden_inputs = array();
        if (is_array($value)) {
            $name = "{$option_name}[$key][]";
            foreach ($value as $index => $val) {
                $hidden_inputs[] =  "<input type='hidden' name='$name' value='$val' class='hidden-values' data-id='$id' data-part='$index' data-key='$key'>";
            }
        } else {
            $name = "{$option_name}[$key]";
            $hidden_inputs[] =  "<input type='hidden' name='$name' value='$value' class='hidden-values' data-id='$id' data-key='$key'>";
        }
        return "$value_str" . implode('', $hidden_inputs);
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
            'name'              => __('Name'),
            'oc_id'             => __('OC Video ID'),
            'class'             => __('CSS Class'),
            'usepermissions'    => __('Restrict video Access'),
            'permissions'       => __('Permission'),
        );
        if (!empty($this->get_actions())) {
            $columns['actions'] = __('Actions');
        }
        return $columns;
    }

    private function set_sotable_columns() {
        $sortable_columns = array(
            // 'name'            => array('se_name',false),
            // 'usepermissions'  => array('se_usepermissions',false),
        );
        return $sortable_columns;
    }

    private function get_actions() {
        $actions = array(
            'edit' => "<a href='#' class='oc-admin-se-edit' data-id='%s'><span class='dashicons dashicons-edit'></span></a>",
            'delete' => "<a href='#' class='oc-admin-se-delete' data-id='%s'><span class='dashicons dashicons-trash'></span></a>"
        );
        return $actions;
    }

    public function save_limit_se_ajax() 
    {
        $user = wp_get_current_user();
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $se_per_page = sanitize_key(absint($_POST['oc_se_table_limit']));
        if (!$se_per_page) {
            $se_per_page = 10;
        }
        $opencast_options['activetabpane'][$user->ID] = $this->activetabpane;
        $opencast_options['singleepisode_table_per_page'][$user->ID] = $se_per_page;
        update_option( OPENCAST_OPTIONS, $opencast_options );
        $response = array(
            'success' => true
        );
        wp_send_json($response);
        wp_die();
    }

    public function delete_se_ajax() 
    {
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $user = wp_get_current_user();
        $opencast_options['activetabpane'][$user->ID] = $this->activetabpane;
        $response = array();
        $se_list = ((isset($opencast_options['singleepisodelist'])) ? $opencast_options['singleepisodelist'] : array());
        $single_episodes_to_delete = isset( $_POST['se_ids'] ) ? (array) $_POST['se_ids'] : array();
        //sanitizing array
        $single_episodes_to_delete = array_map( 'sanitize_key', $single_episodes_to_delete );
        $deleted_ses = array();
        if (!empty($se_list)) {
            foreach ($single_episodes_to_delete as $key) {
                if (array_key_exists($se_id, $se_list)) {
                    unset($se_list[$se_id]);
                    $deleted_ses[] = $se_id;
                }
            }
            $opencast_options['singleepisodelist'] = $se_list;
            update_option( OPENCAST_OPTIONS , $opencast_options );
            if (!empty($not_deleted = array_diff($single_episodes_to_delete, $deleted_ses))) {
                if (count($not_deleted) == $single_episodes_to_delete) {
                    $response['error'] = __('Unable to delete videos');
                } else {
                    $response['success']['notdeleted']  = $not_deleted;
                    $response['success']['deleted']     = $deleted_ses;
                }
            } else {
                $response['success']['deleted'] = $deleted_ses;
            }
        } else {
            $response['error'] = __('No Episode Lists');
        }
        wp_send_json($response);
        wp_die();
    }

    public function update_se_ajax(){
        $opencast_options = get_option(OPENCAST_OPTIONS);
        $user = wp_get_current_user();
        $opencast_options['activetabpane'][$user->ID] = $this->activetabpane;
        $response = array();
        $se_list = ((isset($opencast_options['singleepisodelist'])) ? $opencast_options['singleepisodelist'] : array());
        $single_episode_id = sanitize_key($_POST['se_id']);
        if ($se_list && isset($se_list[$single_episode_id])) {
            $oc_id = sanitize_key($_POST['oc_id']);
            $class = (!empty($_POST['class']) ?  implode(' ', array_map('sanitize_text_field', explode(' ', $_POST['class']))) : '');
            $usepermissions = sanitize_key($_POST['usepermissions']);
            $usepermissions = (($usepermissions == "true") ? true : false);
            $permissions = isset( $_POST['permissions'] ) ? (array) $_POST['permissions'] : array();
            //sanitizing array
            $permissions = array_map( 'sanitize_key', $permissions );
            $se_list[$single_episode_id]['oc_id'] = $oc_id;
            $se_list[$single_episode_id]['class'] = $class;
            $se_list[$single_episode_id]['usepermissions'] = $usepermissions;
            $se_list[$single_episode_id]['permissions'] = $permissions;

            $opencast_options['singleepisodelist'] = $se_list;
            update_option( OPENCAST_OPTIONS , $opencast_options );
            $response['success'] = __('Successfully Updated');
        } else {
            $response['error'] = __('No Episode Found!');
        }
        wp_send_json($response);
        wp_die();
    }
}

?>