<?php 
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Api\Callbacks;
use Opencast\Api\OCRestAPI;

class AdminCallbacks
{
    public function admin_index()
    {
        return require_once( PLUGIN_DIR . 'views/admin/admin_index.php' );
    }
    
    public function api_option_section()
    {
        echo '<p>' . __('Opencast API connection') . '</p>';
    }

    public function studio_option_section()
    {
        echo '<p>' . __('Opencast studio integration') . '</p>';
    }

    public function episode_option_section()
    {
        echo '<p>' . __('Display Opencast Episodes') . '</p>';
    }
    
    public function single_episode_option_section()
    {
        echo '<p>' . __('Manage Single Episodes') . '</p>';
    }

    public function video_option_section()
    {
        echo '<p>' . __('Below are the Upload Video settings for Opencast.') . '</p>';
    }

    public function opencast_options_validation($input)
    {
        //validation and other process comes here
        // we are dealing with array of data like  $opencast_plugin_general_options[text_example]
        if (array_key_exists('singleepisodelist', $input)) {
            foreach ($input['singleepisodelist'] as $name => $value) {
                if (array_key_exists('usepermissions', $value)) {
                    if ($input['singleepisodelist'][$name]['usepermissions'] == 1) {
                        $input['singleepisodelist'][$name]['usepermissions'] = true;
                    } else {
                        $input['singleepisodelist'][$name]['usepermissions'] = false;
                    }
                }
            }
        }

        if (!array_key_exists('episodeusepermissions', $input)) {
            $input['episodeusepermissions'] = false;
        } else {
            $input['episodeusepermissions'] = true;
        }

        if (!array_key_exists('studiousepermissions', $input)) {
            $input['studiousepermissions'] = false;
        } else {
            $input['studiousepermissions'] = true;
        }

        if (!array_key_exists('uploadpublistoengage', $input)) {
            $input['uploadpublistoengage'] = false;
        } else {
            $input['uploadpublistoengage'] = true;
        }

        return $input;
    }


    public function get_callback($type)
    {
        foreach (get_class_methods($this) as $method) {
            if (strtolower($method) == "input{$type}field") {
                return $method;
            }       
        }
    }

    public function inputCheckboxField($args)
    {
        $option = get_option($args['option_name']);
        $value = (isset($option[$args['label_for']])) ? $option[$args['label_for']] : true;
        $checked = '';
        if ($value) {
            $checked = 'checked';
        }
        $class = (isset($args['class'])) ? $args['class'] : '';
        $description = (isset($args['description'])) ? $args['description'] : '';
        $default = (isset($args['default'])) ? $args['default'] : '';
        $required = (isset($args['required'])) ? " required='{$args['required']}' " : '';
        $placeholder = (isset($args['placeholder'])) ? __(esc_html( $args['placeholder'] )) : '';
        $id = (isset($args['label_for']) && !empty($args['label_for'])) ? " id='{$args['label_for']}' " : '';
        $data_attr = '';
        if (isset($args['data']) && is_array($args['data'])) {
            foreach ($args['data'] as $key => $value) {
                $data_attr .= " data-{$key}='{$value}' ";
            }
        }
        
        $input      = "<input $id type='checkbox' $data_attr class='regular-checkbox $class' name='{$args['option_name']}[{$args['label_for']}]' $checked placeholder='$placeholder' $required>";
        $input_default = $default ? "<span class='input-default'>Default: $default</span>" : '';
        $input_desc = $description  ? "<span class='input-desc'>$description</span>" : '';

        echo "<div class='input' >" . $input . $input_default . $input_desc . "</div>";
    }

    public function inputTextField($args)
    {
        $option = get_option($args['option_name']);
        $value = (isset($option[$args['label_for']])) ? esc_attr($option[$args['label_for']]) : '';
        $class = (isset($args['class'])) ? $args['class'] : '';
        $type = (isset($args['type'])) ? $args['type'] : 'text';
        $description = (isset($args['description'])) ? $args['description'] : '';
        $default = (isset($args['default'])) ? $args['default'] : '';
        $required = (isset($args['required'])) ? " required='{$args['required']}' " : '';
        $placeholder = (isset($args['placeholder'])) ? __(esc_html( $args['placeholder'] )) : '';
        $id = (isset($args['label_for']) && !empty($args['id'])) ? " id='{$args['label_for']}' " : '';

        $input      = "<input type='$type' $id class='regular-text $class' name='{$args['option_name']}[{$args['label_for']}]' value='$value' placeholder='$placeholder' $required>";
        $input_default = $default ? "<span class='input-default'>Default: $default</span>" : '';
        $input_desc = $description  ? "<span class='input-desc'>$description</span>" : '';

        echo "<div class='input' >" . $input . $input_default . $input_desc . "</div>";
    }

    public function inputSelectField($args)
    {
        $option = get_option($args['option_name']);
        $values = (isset($option[$args['label_for']])) ? $option[$args['label_for']] : '';
        if (!is_array($values)) {
            $values = array($values);
        }
        $class = (isset($args['class'])) ? $args['class'] : '';
        $description = (isset($args['description'])) ? __(esc_html( $args['description'] )) : '';
        $default = (isset($args['default'])) ? __(esc_html( $args['default'] )) : '';
        $required = (isset($args['required'])) ? " required='{$args['required']}' " : '';
        $placeholder = (isset($args['placeholder'])) ? __(esc_html( $args['placeholder'] )) : '';
        $id = (isset($args['label_for']) && !empty($args['label_for'])) ? " id='{$args['label_for']}' " : '';
        $options = (isset($args['options'])) ? $args['options'] : array();
        $multi = (isset($args['multi']) && $args['multi'] == true) ? "multiple='multiple' data-maxsize=" . count($options) : '';

        $input = "<select style='width: 60%' $id $multi class='oc-select2 $class' name='{$args['option_name']}[{$args['label_for']}]" .($multi ? '[]' : '') . "' placeholder='$placeholder' $required>";
        if (!$multi) {
            $input .= "<option></option>";
        }
        foreach ($options as $option_key => $option_value) {
            if (is_array($values) && in_array($option_key, $values)) {
                $input .= sprintf($option_value, 'selected');
            } else {
                $input .= sprintf($option_value, '');
            }
        }                 
        $input .= "</select>";
        $input_default = $default ? "<span class='input-default'>Default: $default</span>" : '';
        $input_desc = $description  ? "<span class='input-desc'>$description</span>" : '';

        echo "<div class='input' >" . $input . $input_default . $input_desc . "</div>";
    }

    public function inputSingleEpisodeTableField($args)
    {
        $option = get_option($args['option_name']);
        $list = (isset($option[$args['label_for']])) ? $option[$args['label_for']] : array();
        $controller = (isset($args['controller'])) ? $args['controller'] : null;
        $option_name = "{$args['option_name']}[{$args['label_for']}]";
        if ($controller) {
            $controller->generate_table($list, $this->get_wp_roles(), $option_name);
        }
        // echo 'Empty';
    }


    public function get_wp_roles($type = '') {
        $roles = wp_roles();
        $wp_roles = array();
        if (!$roles) {
            return $wp_roles;
        }
        
        switch ($type) {
            case 'dropdown':
                foreach ($roles->role_names as $role_key => $role_name) {
                    $wp_roles[$role_key] = "<option value='$role_key' %s>" . __($role_name) . "</option>";
                }
                break;
            
            default:
                $wp_roles = $roles->role_names;
                break;
        }

        return $wp_roles;
    }

    public function get_existing_workflows($tag = '', $as_options = true) {
        $existing_workflows = array();
        $request = new OCRestAPI();
        if ($returnedresults = $request->oc_get("/api/workflow-definitions?filter=tag:$tag")) {
           foreach ($returnedresults as $workflow) {
                $existing_workflows[$workflow['identifier']] = ((array_key_exists('title', $workflow)) ? $workflow['title'] : $workflow['identifier']);
           }
        }
        if ($as_options) {
            $result_options = array();
            foreach ($existing_workflows as $workflow_id => $workflow_title) {
                $result_options[$workflow_id] = "<option value='$workflow_id' %s>" . __($workflow_title) . "</option>";
            }
            return $result_options;
        }
        return $existing_workflows;
    }
}

?>