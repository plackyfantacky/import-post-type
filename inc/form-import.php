<?php

function do_import_tab() {
    if(isset($_POST['import-post-type-nonce'])) {
        if(wp_verify_nonce($_POST['import-post-type-nonce'], 'import-post-type')) {
            
            //check if file has been uploaded
            switch(true) {
                case (isset($_FILES['file_to_import']['error']) && $_FILES['file_to_import']['error'] != 0):
                    echo '<div class="notice notice-error"><p>File error: ' . $_FILES['file_to_import']['error'] . '</p></div>';
                break;
                case $_FILES['file_to_import']['size'] == 0:
                    echo '<div class="notice notice-error"><p>No file uploaded.</p></div>';
                break;
                case $_FILES['file_to_import']['type'] != 'text/plain' && !is_readable($_FILES['file_to_import']['tmp_name']):
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
                    $post_content = $_POST['import-post-content'];

                    unset($_POST);
                    unset($post);

                    
                    $post_data = [
                        'post_title' => $title,
                        'post_name' => $slug,
                        'post_content' => $post_content,
                        'post_type' => $post_type,
                        'post_status' => $post_status,
                        'post_author' => $post_author,
                        'post_date' => $post_date,
                    ];

                    $post_meta = [];
                    $meta_keys = $_POST['meta_key'];
                    $meta_values = $_POST['meta_value'];
                    foreach($meta_keys as $key => $meta_key) {
                        $post_meta[$meta_key] = $meta_values[$key];
                    }

                    $post_terms = [];
                    $taxonomies = $_POST['taxonomy'];
                    $terms = $_POST['term'];
                    foreach($taxonomies as $key => $taxonomy) {
                        $post_terms[$taxonomy][] = $terms[$key];
                    }

                    $result = false;

                    switch($method) {
                        case "new":
                            $result = wp_insert_post($post_data);
                            if($result) {
                                foreach($post_meta as $meta_key => $meta_value) {
                                    add_post_meta($result, $meta_key, $meta_value);
                                }
                                foreach($terms as $taxonomy => $term_values) {
                                    foreach($term_values as $term_value) {
                                        wp_set_object_terms($result, $term_value, $taxonomy);
                                    }
                                }
                            }
                            
                        break;
                        case "update":
                            $post_data['ID'] = $post_id;
                            $post_data['post_modified'] = $post_modified;
                            $result = wp_update_post($post_data);
                            if($result) {
                                foreach($post_meta as $meta_key => $meta_value) {
                                    update_post_meta($result, $meta_key, $meta_value);
                                }
                                foreach($terms as $taxonomy => $term_values) {
                                    foreach($term_values as $term_value) {
                                        wp_set_object_terms($result, $term_value, $taxonomy);
                                    }
                                }
                            }
                        break;
                        default:
                            echo '<div class="notice notice-error"><p>Invalid import method.</p></div>';
                        break;
                    }

                    echo (isset($result)) ? 
                        '<div class="notice notice-success"><p>Post imported successfully.</p></div>'
                            :
                        '<div class="notice notice-error"><p>Post import failed.</p></div>';        
                break;
            }
        } else {
            echo '<div class="notice notice-error"><p>Invalid operation.</p></div>';
        }
    }
    render_form();
}

function render_form() {
    ?>
    <form id="import_post_form" method="post" action="tools.php?page=import-post-type" enctype="multipart/form-data">
        <?php wp_nonce_field('import-post-type', 'import-post-type-nonce'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">File <span class="required">*</span></th>
                <td><input id="file_to_import" type="file" name="file_to_import" value="" /></td>
            </tr>
            <!-- title -->
            <tr valign="top">
                <th scope="row">Title <span class="required">*</span></th>
                <td><input id="post_title" type="text" name="import-title" value="" style="width:50%" /></td>
            </tr>
            <!-- slug -->
            <tr valign="top">
                <th scope="row">Slug <span class="required">*</span></th>
                <td><input id="post_name" type="text" name="import-slug" value="" style="width:50%" /></td>
            </tr>
            <!-- post id -->
            <tr valign="top">
                <th scope="row">Post ID</th>
                <td>
                    <input id="post_id" type="text" name="import-post-id" value="" size="3" />
                    <p class="description">If not specified, a new one will be generated. If specified, that post will be overridden.</p>
                </td>
            </tr>
            <!-- post type -->
            <tr valign="top">
                <th scope="row">Post Type <span class="required">*</span></th>
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
                <th scope="row">Post Status <span class="required">*</span></th>
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
                <th scope="row">Post Author <span class="required">*</span></th>
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
                <th scope="row">Post Date <span class="required">*</span></th>
                <td><input id="post_date" type="text" name="import-post-date" value="" /></td>
            <!-- post modified -->
            <tr valign="top">
                <th scope="row">Post Modified (optional)</th>
                <td><input id="post_modified" type="text" name="import-post-modified" value="" /></td>
            </tr>
            <!-- post content -->
            <tr valign="top">
                <th scope="row">Post Content</th>
                <td><textarea id="post_content" name="import-post-content"  style="width:100%;height:20rem"></textarea></td>
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
        <hr />
        <div style="display:flex;align-items:center;gap:1rem;">
            <h4>Terms</h4>
            <a href="#" class="add_terms" style="display:flex;align-items:center;gap:0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#437738" viewBox="0 0 20 20">
                    <path d="M10 0c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10zM15 11h-4v4h-2v-4h-4v-2h4v-4h2v4h4v2z"/>
                </svg>
                <span>Add Row</span>
            </a>
        </div>
        <table id="terms_table" class="form-table" style="width:75%;border-collapse:collapse;border-top:1px solid #CCC;border-left:1px solid #CCC;" cellpadding="0">
            <thead>
                <tr valign="top">
                    <th scope="col" style="padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC;width:50%;">Taxonomy</th>
                    <th scope="col" style="padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC;width:50%;">Term</th>
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
        <?php submit_button('Save Changes','primary', 'import_post_type', false); ?>
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
    <table id="terms_template" style="display:none">
        <tr valign="top">
            <td style="width:50%;padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC;vertical-align:top"><input type="text" class="taxonomy" name="taxonomy[]" value="" style="width:100%" /></td>
            <td style="width:50%;padding:0.5rem;border-bottom:1px solid #CCC;border-right:1px solid #CCC;position:relative">
            <input type="text" class="term" name="term[]" value="" style="width:100%" />
                <div style="position:absolute;right:-3rem;top:1rem;display:flex;gap:0.25rem">
                    <a href="#" class="add_terms" style="outline:none"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#437738" viewBox="0 0 20 20"><path d="M10 0c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10zM15 11h-4v4h-2v-4h-4v-2h4v-4h2v4h4v2z"/></svg></a>
                    <a href="#" class="remove_terms" style="outline:none"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ae2d2d" viewBox="0 0 20 20"><path d="M10 0c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10zM15 11h-10v-2h10v2z"/></svg></a>
                </div>
            </td>
        </tr>
    </table>
    <?php
}

function debug_this($variable) {
    $location = get_stylesheet_directory() . '/avada_backup/debug.txt';
    $handle = fopen($location, 'w');
    //timestamp : $variable . PHP_EOL
    //fwrite($handle, date('Y-m-d H:i:s') . ' : ' . var_export($variable, true) . PHP_EOL . PHP_EOL);
    fwrite($handle, date('Y-m-d H:i:s') . ' : ' . json_encode($variable, JSON_PRETTY_PRINT). PHP_EOL . PHP_EOL);
    fclose($handle);
}