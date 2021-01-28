<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Base;

class OCEnqueue
{
    /**
     * Class registeration.
     */
    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'assets_enqueue']);
    }

    /**
	 * put assets in place
	 */
    function assets_enqueue()
    {
        //css
        wp_enqueue_style( 'select2_css', OPENCAST_PLUGIN_DIR_URL . 'src/vendors/select2/dist/css/select2.min.css', array(), '4.0.13', 'all' );
        wp_enqueue_style( 'pagination_css', OPENCAST_PLUGIN_DIR_URL . 'src/vendors/pagination/pagination.css', array(), '2.1.5', 'all' );
        wp_enqueue_style( 'sweetalert2_css', OPENCAST_PLUGIN_DIR_URL . 'src/vendors/sweetalert2/sweetalert2.css', array(), '9.15.2', 'all' );
        wp_register_style( 'opencast-base.css', OPENCAST_PLUGIN_DIR_URL . 'src/css/opencast-base.css', array(), OPENCAST_PLUGIN_VERSION );
        wp_enqueue_style( 'opencast-base.css');

        //javascripts

        wp_register_script( 'select2_js', OPENCAST_PLUGIN_DIR_URL . 'src/vendors/select2/dist/js/select2.min.js', array('jquery'), '4.0.13', true );
        wp_enqueue_script('select2_js');

        wp_register_script( 'pagination_js', OPENCAST_PLUGIN_DIR_URL . 'src/vendors/pagination/pagination.js', array('jquery'), '2.1.5' );
        wp_enqueue_script('pagination_js');

        wp_register_script( 'sweetalert2_js', OPENCAST_PLUGIN_DIR_URL . 'src/vendors/sweetalert2/sweetalert2.js', array('jquery'), '9.15.2' );
        wp_enqueue_script('sweetalert2_js');

        wp_register_script( 'opencast-base.js', OPENCAST_PLUGIN_DIR_URL . 'src/js/opencast-base.js', array('jquery', 'select2_js', 'pagination_js', 'sweetalert2_js'), OPENCAST_PLUGIN_VERSION );
        wp_enqueue_script( 'opencast-base.js');
        wp_localize_script( 'opencast-base.js', 'delete_confirm_data',
            array( 
                'title'                      => __('Are you sure to delete Video(s)?'),
                'text'                       => __('The Video(s) will be permanently deleted.'),
                'confirm_btn'                => __('Yes'),
                'cancel_btn'                 => __('No'),
                'result_success_title'       => __('Deleted'),
                'result_success_msg'         => __('Video(s) successfully deleted'),
                'result_success_partial_msg' => __('There are some Video(s) that could not be deleted!'),
                'result_error_title'         => __('Error!'),
            )
        );
        wp_localize_script( 'opencast-base.js', 'delete_se_confirm_data',
            array( 
                'title'                      => __('Are you sure to delete Episode(s)?'),
                'text'                       => __('The Setting of selected Episode(s) will be permanently deleted and reset.'),
                'confirm_btn'                => __('Yes'),
                'cancel_btn'                 => __('No'),
                'result_success_title'       => __('Deleted'),
                'result_success_msg'         => __('Episode successfully deleted'),
                'result_success_partial_msg' => __('There are some Episode(s) that could not be deleted!'),
                'result_error_title'         => __('Error!'),
            )
        );
        wp_localize_script( 'opencast-base.js', 'update_se_dialog_data',
            array( 
                'save_btn'              => __('Save Changes'),
                'cancel_btn'            => __('Cancel'),
                'result_success_msg'    => __('Episode successfully updated.'),
                'result_error_msg'      => __('Episode failed to update!'),
            )
        );
    }
}