<?php

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
            'author' => $post->post_author,
            'content' => $post->post_content
        );
        
        $post_meta = get_post_meta($post->ID);
        foreach($post_meta as $key => $value) {
            $post_details['meta'][$key] = $value[0];
        }

        $post_terms = get_the_terms($post->ID, get_object_taxonomies($post->post_type));
        foreach($post_terms as $term) {
            $post_details['terms'][$term->taxonomy][] = $term->name;
        }
        
        $output = json_encode($post_details, JSON_PRETTY_PRINT);

        $shortcodes = get_stylesheet_directory() . '/avada_backup/' . $post->post_type . '/' . $post->post_name . '_' . $post->ID . '.json';
        
        if(!file_exists(dirname($shortcodes))) {
            mkdir(dirname($shortcodes), 0755, true);
        }

        $handle = fopen($shortcodes, 'w');
        fwrite($handle, $output);        
        fclose($handle);
    });