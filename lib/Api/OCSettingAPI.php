<?php
/**
 * @package OpencastPlugin   
*/

namespace Opencast\Api;

class OCSettingAPI
{
    public $admin_pages = array();
    public $admin_subpages = array();

    public $settings = array();
    public $sections = array();
    public $fields = array();

    /**
     * Class registeration.
     */
    public function register()
    {
        if (!empty($this->admin_pages)) {
            add_action( 'admin_menu', [$this, 'add_admin_menu']);
        }

        if (!empty($this->settings)) {
            add_action( 'admin_init', [$this, 'registerCustomFields']);
        }
    }

    /**
     * sets array pages and return itself
     * 
     * @param array $pages list of admin pages
     * @return class $this this class
     */
    public function addPages(array $pages)
    {
        $this->admin_pages = $pages;

        return $this;
    }

    /**
     * sets array subpages and return itself
     * 
     * @param array $pages list of admin sub pages
     * @return class $this this class
     */
    public function addSubPages(array $pages)
    {
        $this->admin_subpages = array_merge($this->admin_subpages, $pages);

        return $this;
    }

    /**
     * sets array pages and return itself
     * 
     * @param array $pages list of admin pages
     * @return class $this this class
     */
    public function withSubPages(string $title = null)
    {
        if (empty($this->admin_pages)) {
            return $this;
        }

        $admin_page = $this->admin_pages[0];

        $subpages = [
            [
                'parent_slug' => $admin_page['menu_slug'],
                'page_title' => $admin_page['page_title'],
                'menu_title' => $title ? $title : $admin_page['menu_title'],
                'capability' => $admin_page['capability'],
                'menu_slug' => $admin_page['menu_slug'],
                'callback' => $admin_page['callback'],
            ]
        ];

        $this->admin_subpages = $subpages;

        return $this;
    }

    /**
     * Loops through the admin pages and trigger the add_menu_page action
     */
    public function add_admin_menu()
    {
        foreach ($this->admin_pages as $page)
        {
            add_menu_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position']);
        }
        
        foreach ($this->admin_subpages as $page)
        {
            add_submenu_page( $page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback']);
        }
    }


    /**
     * sets array settings and return itself
     * 
     * @param array $settings list of admin custom settings
     * @return class $this this class
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * sets array sections and return itself
     * 
     * @param array $sections list of admin custom sections
     * @return class $this this class
     */
    public function setSections(array $sections)
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * sets array fields and return itself
     * 
     * @param array $fields list of admin custom fields
     * @return class $this this class
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Custom Fields registeration.
     */
    public function registerCustomFields()
    {
        //register setting
        foreach ($this->settings as $setting)
        {
            register_setting( 
                $setting['option_group'], 
                $setting['option_name'], 
                (isset($setting['callback'])) ? $setting['callback'] : null 
            );
        }

        //add setting section
        foreach ($this->sections as $section)
        {
            add_settings_section( 
                $section['id'], 
                $section['title'], 
                (isset($section['callback'])) ? $section['callback'] : null, 
                $section['page'] 
            );
        }

        //add settings field
        foreach ($this->fields as $field)
        {
            add_settings_field( 
                $field['id'], 
                $field['title'], 
                (isset($field['callback'])) ? $field['callback'] : null, 
                $field['page'], 
                $field['section'], 
                (isset($field['args'])) ? $field['args'] : null
            );
        }
    }
}


