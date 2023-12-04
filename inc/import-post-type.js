jQuery(document).ready(function($) {

    //listen for file upload. input id is 'file_to_import'
    $('#file_to_import').change(function() {
        let file = this.files[0]
        let reader = new FileReader()
        reader.onload = function(progressEvent){
            //pass the whole file as json
            let file_contents = JSON.parse(this.result)
            
            if(file_contents['title'] != undefined) { $('#post_title').val(file_contents['title']) } //post title
            if(file_contents['slug'] != undefined) { $('#post_name').val(file_contents['slug']) } //post slug
            if(file_contents['id'] != undefined) { $('#post_id').val(file_contents['id']) } //post id
            if(file_contents['date'] != undefined) { $('#post_date').val(file_contents['date']) } //date
            if(file_contents['modified'] != undefined) { $('#post_modified').val(file_contents['modified']) } //modified
            if(file_contents['type'] != undefined) { $('#post_type').val(file_contents['type']) } //post type (select dropdown)
            if(file_contents['status'] != undefined) { $('#post_status').val(file_contents['status']) } //post status (select dropdown)
            if(file_contents['author'] != undefined) { $('#post_author').val(file_contents['author']) } //author (select dropdown)
            if(file_contents['content'] != undefined) { $('#post_content').val(file_contents['content']) } //content (textarea)
            
            //meta (dynamic rows)
            if(file_contents['meta'] != undefined) {
                //loop through each meta key/value pair
                $.each(file_contents['meta'], function(key, value) {
                    //find the meta_template table and copy the first row to the end of the meta_table
                    let meta_template = $('#meta_template tbody tr:first-child')
                    
                    $newRow = $(meta_template).clone()
                    $newRow.find('input.meta_key').val(key)
                    $newRow.find('input.meta_value').val(value)
                    $newRow.appendTo('#meta_table tbody')
                    
                })
            }

            //taxonomies/terms (dynamic rows)
            if(file_contents['terms'] != undefined) {
                //loop through each taxonomy
                $.each(file_contents['terms'], function(taxonomy, terms) {
                    let meta_template = $('#terms_template tbody tr:first-child')
                    //each 'terms' has a flat array of terms. loop through each term and add a row
                    $.each(terms, function(key, term) {
                        $newRow = $(meta_template).clone()
                        $newRow.find('input.taxonomy').val(taxonomy)
                        $newRow.find('input.term').val(term)
                        $newRow.appendTo('#terms_table tbody')
                    });
                })
            }
        }
        reader.readAsText(file)
    })

    $(document).on('click', 'a.add_meta', function(e) {
        //find the meta_template table and copy the first row to the end of the meta_table
        e.preventDefault()
        let meta_template = $('#meta_template tbody tr:first-child')
        let meta_table = $('#meta_table tbody')
        $(meta_template).clone().appendTo(meta_table)
    })

    $(document).on('click', 'a.remove_meta', function(e) {
        //remove the row
        e.preventDefault()
        $(this).closest('tr').remove()
    })

    $(document).on('click', 'a.add_terms', function(e) {
        //find the meta_template table and copy the first row to the end of the meta_table
        e.preventDefault()
        let taxonomy_template = $('#terms_template tbody tr:first-child')
        let taxonomy_table = $('#terms_table tbody')
        $(taxonomy_template).clone().appendTo(taxonomy_table)
    })

    $(document).on('click', 'a.remove_terms', function(e) {
        //remove the row
        e.preventDefault()
        $(this).closest('tr').remove()
    })

    $('#import_post_form').submit(function(e) {
        //validate form
        let errors = []
        
        if($('#file_to_import').val() == '') { errors.push('File to import is required') } //file
        if($('#post_title').val() == '') { errors.push('Post title is required') } //post title
        if($('#post_name').val() == '') { errors.push('Post slug is required') } //post slug
        if($('#post_date').val() == '') { errors.push('Post date is required') } //date
        if($('#post_author').val() == '') { errors.push('Post author is required') } //author
        if($('#post_status').val() == '') { errors.push('Post status is required') } //status
        if($('#post_type').val() == '') { errors.push('Post type is required') } //type

        
        //output the errors
        if(errors.length > 0) {
            e.preventDefault()
            let error_html = '<ul>'
            $.each(errors, function(key, value) {
                error_html += '<li>' + value + '</li>'
            })
            error_html += '</ul>'
            //create an element with the id 'errors' and insert before '.form-table'
            if($('#errors').length == 0) {
                $('<div id="errors" class="notice notice-error" style="margin-top:1rem;"></div>').insertBefore('#import_post_form')
            }
            $('#errors').html(error_html)
        } else {
            //no errors, submit the form
            $('#errors').html('')
        }
    });
})