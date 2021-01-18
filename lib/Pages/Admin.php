<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Pages;

use Opencast\Api\SettingAPI;
use Opencast\Api\Callbacks\AdminCallbacks;
use Opencast\Base\VideoManagerController;
use Opencast\Base\SingleEpisodeTableController;

class Admin
{
    public $settings;
    public $callbacks;

    public function __construct()
    {
        $this->settings = new SettingAPI();
        $this->callbacks = new AdminCallbacks();
    }

    /**
     * Class registeration.
     */
    public function register()
    {
        $this->settings
                ->addPages($this->get_admin_pages())
                ->withSubPages('General')
                ->addSubPages($this->get_admin_subpages())
                ->setSettings($this->get_settings())
                ->setSections($this->get_sections())
                ->setFields($this->get_fields())
                ->register();
    }

    /**
	 * Returns a list of admin pages
     * @return array admin pages list
	 */
    private function get_admin_pages()
    {
        return [
            [
                'page_title' => 'Opencast Setting',
                'menu_title' => 'Opencast',
                'capability' => 'manage_options',
                'menu_slug' => OPENCAST_OPTIONS,
                'callback' => [$this->callbacks, 'admin_index'],
                'icon_url' => 'dashicons-opencast',
                'position' => 110,
            ]
        ];
    }

    /**
	 * Returns a list of admin subpages
     * @return array admin subpages list
	 */
    private function get_admin_subpages()
    {
        return [
            [
                'parent_slug' => OPENCAST_OPTIONS,
                'page_title' => __('Video Manager'),
                'menu_title' => __('Video Manager'),
                'capability' => 'manage_options',
                'menu_slug' => OPENCAST_VIDEOS_MANAGER,
                'callback' => [new VideoManagerController(), 'admin_video_manager_index'],
            ]
        ];
    }

    /**
	 * Returns a list of admin custom settings
     * @return array admin custom settings list
	 */
    public function get_settings()
    {
        return [
            [
                'option_group' => 'opencast_plugin_options_group', 
                'option_name' => OPENCAST_OPTIONS,
                'callback' => [$this->callbacks, 'opencast_options_validation']
            ],
        ];

    }

     /**
	 * Returns a list of admin custom sections
     * @return array admin custom sections list
	 */
    public function get_sections()
    {
        return [
            [
                'id' => 'opencast_api_option_section', 
                'title' => __('API Settings'), 
                'callback' => [$this->callbacks, 'api_option_section'], 
                'page' => OPENCAST_OPTIONS,
            ],
            [
                'id' => 'opencast_video_option_section', 
                'title' => __('Upload Video Settings'), 
                'callback' => [$this->callbacks, 'video_option_section'], 
                'page' => OPENCAST_OPTIONS
            ],
            [
                'id' => 'opencast_studio_option_section', 
                'title' => __('Studio Settings'), 
                'callback' => [$this->callbacks, 'studio_option_section'], 
                'page' => OPENCAST_OPTIONS
            ],
            [
                'id' => 'opencast_episode_option_section', 
                'title' => __('Episodes Settings'), 
                'callback' => [$this->callbacks, 'episode_option_section'], 
                'page' => OPENCAST_OPTIONS
            ],
            [
                'id' => 'opencast_single_episode_option_section', 
                'title' => __('Single Episodes Settings'), 
                'callback' => [$this->callbacks, 'single_episode_option_section'], 
                'page' => OPENCAST_OPTIONS
            ]
        ];

    }

    /**
     * Returns a list of admin custom fields
     * @return array admin custom fields list
     */
    public function get_fields()
    {
        $new_fields = [];
        foreach ($this->set_section_setting_fields() as $section => $fields) {
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $field['page'] = OPENCAST_OPTIONS;
                    $field['args']['option_name'] = OPENCAST_OPTIONS;
                    $field['section'] = $section;
                    $field['callback'] = [$this->callbacks, $this->callbacks->get_callback($field['type'])];
                    unset($field['type']);
                    $new_fields[] = $field;
                }
            }
        }
        return $new_fields;
    }

    /**
     * Sets an array of admin section setting with fields
     * @return array admin custom fields with section settings list
     */
    private function set_section_setting_fields()
    {
        return [
            'opencast_api_option_section' => [
                [
                    'id' => 'seriesid',
                    'title' => __('Opencast Series ID'),
                    'type' => 'text',
                    'args' => [
                        'required' => true,
                        'label_for' => 'seriesid',
                        'class' => '',
                        'placeholder' => 'Series ID',
                        'type' => 'text',
                        'description' => __('Series ID is used to group the videos in the Opencast server.')
                    ]
                ],
                [
                    'id' => 'apiurl',
                    'title' => __('Opencast API url'),
                    'type' => 'text',
                    'args' => [
                        'required' => true,
                        'label_for' => 'apiurl',
                        'class' => '',
                        'placeholder' => 'opencast.example.com',
                        'description' => __('Setup the base url of the Opencast system, for example: opencast.example.com')
                    ]
                ],
                [
                    'id' => 'apiusername',
                    'title' => __('Username for API calls'),
                    'type' => 'text',
                    'args' => [
                        'required' => true,
                        'label_for' => 'apiusername',
                        'class' => '',
                        'placeholder' => 'Username',
                        'description' => __('For all calls to the API, this plugin uses this user. Authorization is done by adding suitable roles to the call')
                    ]
                ],
                [
                    'id' => 'apipassword',
                    'title' => __('Password for API user'),
                    'type' => 'text',
                    'args' => [
                        'required' => true,
                        'label_for' => 'apipassword',
                        'class' => '',
                        'placeholder' => 'Password for the Username',
                        'type' => 'password',
                        'description' => __('Setup a password for the super user, who does the api calls.')
                    ]
                ],
                [
                    'id' => 'apitimeout',
                    'title' => __('Connection timeout (seconds)'),
                    'type' => 'text',
                    'args' => [
                        'label_for' => 'apitimeout',
                        'class' => '',
                        'placeholder' => 'Connection timeout in seconds',
                        'type' => 'number',
                        'description' => __('Setup the time in seconds while moodle is trying to connect to opencast until timeout'),
                        'default' => '1 Second'
                    ]
                ],
                [
                    'id' => 'consumerkey',
                    'title' => __('Consumer Key'),
                    'type' => 'text',
                    'args' => [
                        'required' => true,
                        'label_for' => 'consumerkey',
                        'class' => '',
                        'placeholder' => 'LTI Consumer Key',
                        'type' => 'text',
                        'description' => __('LTI Consumer key for Opencast Integration.')
                    ]
                ],
                [
                    'id' => 'consumersecret',
                    'title' => __('Consumer Secret'),
                    'type' => 'text',
                    'args' => [
                        'required' => true,
                        'label_for' => 'consumersecret',
                        'class' => '',
                        'placeholder' => 'LTI Consumer Secret',
                        'type' => 'password',
                        'description' => __('LTI Consumer secret for Opencast Integration:')
                    ]
                ],
                [
                    'id' => 'ltiinstructors',
                    'title' => __('LTI Instructor Permission'),
                    'type' => 'select',
                    'args' => [
                        'label_for' => 'ltiinstructors',
                        'placeholder' => 'Instructors Roles',
                        'multi' => true,
                        'description' => __('User Roles that have Instructor Permissions'),
                        'options' => $this->callbacks->get_wp_roles('dropdown'),
                        'default' => 'administrator'
                    ]
                ]
            ],
            'opencast_studio_option_section' => [
                [
                    'id' => 'studiousepermissions',
                    'title' => __('Activate Access Permissions'),
                    'type' => 'checkbox',
                    'args' => [
                        'label_for' => 'studiousepermissions',
                        'class' => 'trigger-disabled-parent',
                        'data' => array(
                            'child' => 'studiopermission'
                        ),
                        'placeholder' => 'Using Access Permissions',
                        'description' => __('Checking Access Permissions against User Roles'),
                        'default' => __('Activated')
                    ]
                ],
                [
                    'id' => 'studiopermission',
                    'title' => __('Access Permissions'),
                    'type' => 'select',
                    'args' => [
                        'label_for' => 'studiopermission',
                        'class' => 'trigger-disabled-child studio-ap disabled',
                        'placeholder' => 'Access Permissions for Studio',
                        'multi' => true,
                        'description' => __('Select the roles to grant access to Studio features'),
                        'options' => $this->callbacks->get_wp_roles('dropdown')
                    ]
                ],
            ],
            'opencast_episode_option_section' => [
                [
                    'id' => 'episodeendpoiturl',
                    'title' => __('Endpoint Url For Lists'),
                    'type' => 'text',
                    'args' => [
                        'label_for' => 'episodeendpoiturl',
                        'class' => '',
                        'placeholder' => 'Endpoint URL',
                        'type' => 'text',
                        'description' => __('In case, lists have to be read from another url. If empty, the default API URL will be used.'),
                        'default' => 'Empty' 
                    ]
                ],
                [
                    'id' => 'episodeseriesid',
                    'title' => __('Extrnal Series ID'),
                    'type' => 'text',
                    'args' => [
                        'label_for' => 'episodeseriesid',
                        'class' => '',
                        'placeholder' => 'Extrnal Series ID',
                        'type' => 'text',
                        'description' => __('In case, there is another series to be listed, If empty, the default series id will be used.'),
                        'default' => 'Empty' 
                    ]
                ],
                [
                    'id' => 'episodepagelimit',
                    'title' => __('Pagination Limit Number'),
                    'type' => 'text',
                    'args' => [
                        'label_for' => 'episodepagelimit',
                        'class' => '',
                        'placeholder' => 'Limit Number',
                        'type' => 'number',
                        'description' => __('Make Pagination based on page limits, when 0 or Empty it does not created pagination'),
                        'default' => '0 or Empty' 
                    ]
                ],
                [
                    'id' => 'episodeusepermissions',
                    'title' => __('Activate Access Permissions'),
                    'type' => 'checkbox',
                    'args' => [
                        'label_for' => 'episodeusepermissions',
                        'class' => 'trigger-disabled-parent',
                        'data' => array(
                            'child' => 'episodepermission'
                        ),
                        'placeholder' => 'Using Access Permissions',
                        'description' => __('Checking Access Permissions against User Roles'),
                        'default' => __('Activated')
                    ]
                ],
                [
                    'id' => 'episodepermission',
                    'title' => __('Access Permissions'),
                    'type' => 'select',
                    'args' => [
                        'label_for' => 'episodepermission',
                        'class' => 'trigger-disabled-child episode-ap disabled',
                        'placeholder' => 'Access Permissions for Episodes list',
                        'multi' => true,
                        'description' => __('Select the roles to grant access to List of Videos'),
                        'options' => $this->callbacks->get_wp_roles('dropdown')
                    ]
                ],
            ],
            'opencast_video_option_section' => [
                [
                    'id' => 'uploadusepermissions',
                    'title' => __('Activate Access Permissions'),
                    'type' => 'checkbox',
                    'args' => [
                        'label_for' => 'uploadusepermissions',
                        'class' => 'trigger-disabled-parent',
                        'data' => array(
                            'child' => 'uploadpermissions'
                        ),
                        'placeholder' => 'Using Access Permissions',
                        'description' => __('Checking Access Permissions against User Roles'),
                        'default' => __('Activated')
                    ]
                ],
                [
                    'id' => 'uploadpermissions',
                    'title' => __('Access Permissions'),
                    'type' => 'select',
                    'args' => [
                        'label_for' => 'uploadpermissions',
                        'class' => 'trigger-disabled-child episode-ap disabled',
                        'placeholder' => 'Access Permissions for Episodes list',
                        'multi' => true,
                        'description' => __('Select the roles to grant access to Upload Videos'),
                        'options' => $this->callbacks->get_wp_roles('dropdown')
                    ]
                ],
                [
                    'id' => 'uploadworkflow',
                    'title' => __('Workflow to start after upload'),
                    'type' => 'select',
                    'args' => [
                        'label_for' => 'uploadworkflow',
                        'placeholder' => 'Workflow to start after upload',
                        'multi' => false,
                        'description' => __('Setup the unique shortname of the workflow, that should be started after succesfully uploading a video file to opencast. If left blank the standard workflow (ng-schedule-and-upload) will be used. Ask for additional workflows that may have been created by the opencast administrator.'),
                        'options' => $this->callbacks->get_existing_workflows('upload', true),
                        'default' => 'upload'
                    ]
                ],
                [
                    'id' => 'uploadfilesize',
                    'title' => __('Max. Upload Filesize (Mb)'),
                    'type' => 'text',
                    'args' => [
                        'label_for' => 'uploadfilesize',
                        'class' => '',
                        'placeholder' => 'Filesize in MB',
                        'type' => 'number',
                        'description' => __('The upload video filesize to be handled by Drag and Drop Zone, if Empty, 256 Mb would be the Max.'),
                        'default' => '2560 Mb'
                    ]
                ],
            ],
            'opencast_single_episode_option_section' => [
                [
                    'id' => 'singleepisodelist',
                    'title' => __(''),
                    'type' => 'singleepisodetable',
                    'args' => [
                        'label_for' => 'singleepisodelist',
                        'class' => '',
                        'controller' => new SingleEpisodeTableController(),
                    ]
                ],
            ]
        ];
    }

}
?>