<?php
    /*
    * Plugin Name: Import Post Type
    * Plugin URI: https://github.com/plackyfantacky/import-post-type
    * Description: Import a single post from a file. Allows you to manually set values for post_ values. Post meta and taxonomy terms coming soon. Go to Tools > Import Post Type to use.
    * Version: 1.0
    * Requires at least: 6.4
    * Requires PHP: 8.1
    * Author: Adam Trickett
    * Author URI: https://ariom.id.au
    */

    require_once('inc/form-import.php');
    require_once('inc/backup-post.php');

    //add plugin settings sets
    add_action('admin_init', function() {
        
        $section_group = 'import-post-type-settings';
        $section_name = 'post-type-backups';

        register_setting($section_group, $section_name);
        
        add_settings_section($section_name, 'Post Type Backups', '', $section_group );
               
        add_settings_field('backup_path', 'Backup Path', 'callback_field_backup_path', $section_group, $section_name);
        add_settings_field('exclude_post_types', 'Exclude Post Types', 'callback_field_exclude_types', $section_group, $section_name);

        function callback_field_backup_path() {
            $value = get_option('import-post-type-settings'); ?>
            <p>Enter the location of where to store post backup files. (Note: If your site is version controlled, its recommended to use a subfolder of the active theme).</p>
            <input type="text" name="import-post-type-settings[backup_path]" value="<?= $value['backup_path'] ?>" style="width:50%" />
        <?php }

        function callback_field_exclude_types($option_group) {
            $value = get_option('import-post-type-settings'); ?>
            <p>Enter a list of post-types to exclude from the backup. (One post-type per line).</p>
            <textarea name="import-post-type-settings[exclude_post_types]" style="width:50%;height:10rem"><?= $value['exclude_post_types']; ?></textarea>
        <?php }
    });


    //add submenu to Tools menu
    add_action('admin_menu', function() {
        add_submenu_page(
            'tools.php',
            'Import Post Type',
            'Import Post Type',
            'manage_options',
            'import-post-type',
            'import_post_type_page'
        );
    });

    //add page content
    function import_post_type_page() {
        if(!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'import';

        $active_import = ($active_tab=='import' ? 'nav-tab-active' : '');
		$active_options = ($active_tab=='options' ? 'nav-tab-active' : '');

        ?>
        <div class="wrap">
            <h1>Import Post Type</h1>
            <nav class="nav-tab-wrapper">
                <a href="tools.php?page=import-post-type" class="nav-tab <?= $active_import; ?>">Import From File</a>
                <a href="tools.php?page=import-post-type&tab=options" class="nav-tab <?= $active_options; ?>">Options</a>
            </nav>
            <?php if($active_tab == 'options') {
                render_options();
            } else {
                do_import_tab();
            } ?>
        </div>
        <?php
    }

    function render_options() {

    ?>
         <form method="post" action="tools.php?page=import-post-type&tab=options">
            <?php settings_fields('import-post-type-settings'); ?>
            <?php do_settings_sections('import-post-type-settings'); ?>
            <?php submit_button(); ?>
         </form>
    <?php }

    add_action('admin_enqueue_scripts', function() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('import-post-type-script', plugins_url('inc/import-post-type.js', __FILE__), ['jquery'], filemtime(plugin_dir_path(__FILE__) . 'inc/import-post-type.js'), true);
    });

    