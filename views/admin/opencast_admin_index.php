<div class="wrap oc-admin-wrapper">
    <h1>Opencast Plugin</h1>
    <?php 
        global $wp_settings_sections, $wp_settings_fields;
        settings_errors();
        $page = OPENCAST_OPTIONS;
        $user = wp_get_current_user();
        $active_tabpane_name = OPENCAST_OPTIONS . "[activetabpane][{$user->ID}]";
        $active_tabpane_value = ((isset(get_option(OPENCAST_OPTIONS)["activetabpane"][$user->ID])) ? get_option(OPENCAST_OPTIONS)["activetabpane"][$user->ID] : "opencast_api_option_section");
    ?>
    

    <ul class="nav nav-tabs">
        <?php
        $active = true;
        foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
            ?>
            <li class="<?php echo (($section['id'] == $active_tabpane_value) ? 'active' : '' )?>">
                <a href="#<?php echo $section['id']; ?>">
                    <?php echo $section['title']; ?>
                </a>
            </li>
            <?php
            $active = false;
        }
        ?>
    </ul>

    <div class="tab-content">
        <form action="options.php" method="post">
            <input id="activetabpane" type="hidden" name="<?php echo $active_tabpane_name; ?>" value="<?php echo $active_tabpane_value; ?>">
        <?php
            settings_fields('opencast_plugin_options_group');
            $active = true;
            foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
                ?>
                <div id="<?php echo $section['id']; ?>" class="tab-pane <?php echo (($section['id'] == $active_tabpane_value) ? 'active' : '' ) ?>">
                    <?php
                    if ( $section['callback'] ) {
                        call_user_func( $section['callback'], $section );
                    }
                    if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
                        echo "<br>This setting is currently empty!";
                        continue;
                    }
                    if ($section['id'] != 'opencast_single_episode_option_section') {
                        echo '<table class="form-table opencast-option-table" role="presentation">';
                        do_settings_fields( $page, $section['id'] );
                        echo '</table>';
                        submit_button();
                    } else {
                        do_settings_fields( $page, $section['id'] );
                    }
                    ?>
                </div>
                <?php
                $active = false;
            }
        ?>
        </form>
    </div>
    
</div>