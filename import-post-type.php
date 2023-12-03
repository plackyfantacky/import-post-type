<?php
    /*
    * Plugin Name: Import Post Type
    * Plugin URI: https://github.com/plackyfantacky/import-post-type
    * Description: Import post type from a file. Allows you to manually set its Post ID.
    * Version: 1.0
    * Requires at least: 6.4
    * Requires PHP: 8.1
    * Author: Adam Trickett
    * Author URI: https://ariom.id.au
    */

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
        } ?>
        <div class="wrap">
            <h1>Import Post Type</h1>
    <?php

        if(isset($_POST['import-post-type-nonce'])) {
            if(wp_verify_nonce($_POST['import-post-type-nonce'], 'import-post-type')) {
                $file = $_FILES['file_to_import'];
                if(isset($_FILES)) {
                    echo '<div class="notice notice-info">' . var_export($_FILES) .'</div>'; 
                }
                //check if file has been uploaded
                switch(true) {
                    case (isset($file['error']) && $file['error'] != 0):
                        echo '<div class="notice notice-error"><p>File error: ' . $file['error'] . '</p></div>';
                    break;
                    case $file['size'] == 0:
                        echo '<div class="notice notice-error"><p>No file uploaded.</p></div>';
                    break;
                    case $file['type'] != 'text/plain' && !is_readable($file['tmp_name']):
                        echo '<div class="notice notice-error"><p>File must be a text file.</p></div>';
                    break;
                    default:
                        $method = $_POST['import-method'];
                        $title = $_POST['import-title'];
                        $slug = $_POST['import-slug'];
                        $post_id = $_POST['import-post-id'];
                        $post_type = $_POST['import-post-type'];
                        $post_status = $_POST['import-post-status'];
                        $post_author = $_POST['import-post-author'];
                        $post_date = $_POST['import-post-date'];
                        $post_modified = $_POST['import-post-modified'];

                        $post_content = file_get_contents($file['name']) || '';

                        if($method = "new") {
                            $post = [
                                'post_title' => $title,
                                'post_name' => $slug,
                                'post_content' => $post_content,
                                'post_type' => $post_type,
                                'post_status' => $post_status,
                                'post_author' => $post_author,
                                'post_date' => $post_date,
                                'post_modified' => $post_modified
                            ];
                            $post_id = wp_insert_post($post);
                            
                            if($post_id) {
                                echo '<div class="notice notice-success"><p>Post imported successfully.</p></div>';
                                render_form();
                            } else {
                                echo '<div class="notice notice-error"><p>Post import failed.</p></div>';
                                render_form();
                            }
                        } else if($method = "update") {
                            $post = [
                                'ID' => $post_id,
                                'post_title' => $title,
                                'post_name' => $slug,
                                'post_content' => $post_content,
                                'post_type' => $post_type,
                                'post_status' => $post_status,
                                'post_author' => $post_author,
                                'post_date' => $post_date,
                                'post_modified' => $post_modified
                            ];
                            $post_id = wp_update_post($post);
                            
                            if($post_id) {
                                echo '<div class="notice notice-success"><p>Post updated successfully.</p></div>';
                            } else {
                                echo '<div class="notice notice-error"><p>Post update failed.</p></div>';
                            }
                        } else {
                            echo '<div class="notice notice-error"><p>Invalid import method.</p></div>';
                        }
                    break;
                }
                render_form();
            } else {
                echo '<div class="notice notice-error"><p>Invalid operation.</p></div>';
                render_form();
            }
        } else {
            //nothing else to do, render form
            render_form();
        } ?></div><?php
    }

    function render_form() { ?>
            <form method="post" action="tools.php?page=import-post-type" enctype="multipart/form-data">
                <?php wp_nonce_field('import-post-type', 'import-post-type-nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">File</th>
                        <td><input id="file_to_import" type="file" name="file_to_import" value="" /></td>
                    </tr>
                    <!-- title -->
                    <tr valign="top">
                        <th scope="row">Title</th>
                        <td><input id="post_title" type="text" name="import-title" value="" style="width:50%" /></td>
                    </tr>
                    <!-- slug -->
                    <tr valign="top">
                        <th scope="row">Slug</th>
                        <td><input id="post_name" type="text" name="import-slug" value="" style="width:50%" /></td>
                    </tr>
                    <!-- post id -->
                    <tr valign="top">
                        <th scope="row">Post ID</th>
                        <td><input id="post_id" type="text" name="import-post-id" value="" size="3" /></td>
                    </tr>
                    <!-- post type -->
                    <tr valign="top">
                        <th scope="row">Post Type</th>
                        <td>
                            <select id="post_type" name="import-post-type">
                                <?php
                                    $post_types = get_post_types();
                                    foreach($post_types as $post_type) {
                                        echo '<option value="' . $post_type . '">' . $post_type . '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <!-- post status -->
                    <tr valign="top">
                        <th scope="row">Post Status (optional)</th>
                        <td>
                            <select id="post_status" name="import-post-status">
                                <option value="draft">Draft</option>
                                <option value="publish">Publish</option>
                                <option value="pending">Pending</option>
                                <option value="private">Private</option>
                                <option value="future">Future</option>
                                <option value="trash">Trash</option>
                            </select>
                        </td>
                    </tr>
                    <!-- post author -->
                    <tr valign="top">
                        <th scope="row">Post Author (optional)</th>
                        <td>
                            <select id="post_author" name="import-post-author">
                                <?php
                                    $users = get_users();
                                    foreach($users as $user) {
                                        echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <!-- post date -->
                    <tr valign="top">
                        <th scope="row">Post Date (optional)</th>
                        <td><input id="post_date" type="text" name="import-post-date" value="" /></td>
                    <!-- post modified -->
                    <tr valign="top">
                        <th scope="row">Post Modified (optional)</th>
                        <td><input id="post_modified" type="text" name="import-post-modified" value="" /></td>
                    </tr>
                </table>
                <hr />
                <div style="display:flex;align-items:center;gap:1rem;">
                    <h4>Custom Fields</h4>
                    <a href="#" class="add_meta" style="display:flex;align-items:center;gap:0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#437738" viewBox="0 0 20 20">
                            <path d="M10 0c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10zM15 11h-4v4h-2v-4h-4v-2h4v-4h2v4h4v2z"/>
                        </svg>
                        <span>Add Row</span>
                    </a>
                </div>
                <table id="meta_table" class="form-table" style="width:75%;border-collapse:collapse;border-top:1px solid #CCC;border-left:1px solid #CCC;" cellpadding="0">
                    <thead>
                        <tr valign="top">
                            <th scope="col" style="padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC;width:50%;">Field Name</th>
                            <th scope="col" style="padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC;width:50%;">Field Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Import Method</th>
                        <td>
                            <div style="margin-bottom:0.5rem;"><input type="radio" name="import-method" value="new" checked="checked" /> Import as new</div>
                            <div style="margin-bottom:0.5rem;"><input type="radio" name="import-method" value="update" /> Override/update if ID already exists</div>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <table id="meta_template" style="display:none">
                <tr>
                    <td style="width:50%;padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC"><input type="text" class="meta_key" name="meta_key[]" value="" style="width:100%" /></td>
                    <td style="width:50%;padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC;position:relative">
                        <input type="text" class="meta_value" name="meta_value[]" value="" style="width:100%" />
                        <div style="position:absolute;right:-3rem;top:1rem;display:flex;gap:0.25rem">
                            <a href="#" class="add_meta" style="outline:none"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#437738" viewBox="0 0 20 20"><path d="M10 0c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10zM15 11h-4v4h-2v-4h-4v-2h4v-4h2v4h4v2z"/></svg></a>
                            <a href="#" class="remove_meta" style="outline:none"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ae2d2d" viewBox="0 0 20 20"><path d="M10 0c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10zM15 11h-10v-2h10v2z"/></svg></a>
                        </div>
                    </td>
                </tr>
            </table>
        <?php
    }

    add_action('admin_enqueue_scripts', function() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('import-post-type-script', plugins_url('import-post-type.js', __FILE__), ['jquery'], filemtime(plugin_dir_path(__FILE__) . 'import-post-type.js'), true);
    });

    //auto output post_content to files upon save
    add_action('save_post', function($post_id) {
        $post = get_post($post_id);
        if($post->post_type == 'revision') { return; } //ignore revisions

        $post_details = array(
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'id' => $post->ID,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'status' => $post->post_status,
            'type' => $post->post_type,
            'author' => $post->post_author
        );
        
        $post_meta = get_post_meta($post->ID);
        foreach($post_meta as $key => $value) {
            $post_details['meta'][$key] = $value[0];
        }

        $post_terms = get_the_terms($post->ID, get_object_taxonomies($post->post_type));
        foreach($post_terms as $term) {
            $post_details['terms'][$term->taxonomy][] = $term->name;
        }
        
        $frontmatter = "<!--" . json_encode($post_details, JSON_PRETTY_PRINT) . '-->' . PHP_EOL . PHP_EOL;

        $shortcodes = get_stylesheet_directory() . '/content/shortcodes/' . $post->post_type . '/' . $post->post_name . '_' . $post->ID . '.txt';
        
        if(!file_exists(dirname($shortcodes))) {
            mkdir(dirname($shortcodes), 0755, true);
        }

        $handle = fopen($shortcodes, 'w');
        fwrite($handle, $frontmatter);        
        fwrite($handle, $post->post_content);
        fclose($handle);
    });