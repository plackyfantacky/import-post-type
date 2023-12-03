jQuery(document).ready(function($) {

    //listen for file upload. input id is 'file_to_import'
    $('#file_to_import').change(function() {
        //read filename
        let filename = $(this).val()
        //filename should be (but not always) be in the format of 'post-name_postID.txt'. split by underscore and file extension
        //remove 'C:\fakepath\' from filename, and then the file extension (everthing after the last '.')
        filename = filename.replace('C:\\fakepath\\', '')
        filename = filename.split('.')[0]
        let filename_parts = filename.split('_')
        //console.log(filename_parts)
        //output the file contents to the textarea 'debug'
        let file = this.files[0]
        let reader = new FileReader()
        reader.onload = function(progressEvent){
            //parse the first html commment as json
            let file_contents = this.result
            file_contents = file_contents.split('<!--')
            file_contents = file_contents[1].split('-->')
            file_contents = file_contents[0]
            file_contents = JSON.parse(file_contents)
            //console.log(file_contents)
            //map the array key/value pairs to the form fields
            //post title
            if(file_contents['title'] != undefined) {
                $('#post_title').val(file_contents['title'])
            }
            //post slug
            if(file_contents['slug'] != undefined) {
                $('#post_name').val(file_contents['slug'])
            } else {
                $('#post_name').val(filename_parts[0])
            }
            //post id
            if(file_contents['id'] != undefined) {
                $('#post_id').val(file_contents['id'])
            } else {
                $('#post_id').val(filename_parts[1])
            }
            //date
            if(file_contents['date'] != undefined) {
                $('#post_date').val(file_contents['date'])
            }
            //modified
            if(file_contents['modified'] != undefined) {
                $('#post_modified').val(file_contents['modified'])
            }
            //post type (select dropdown)
            if(file_contents['type'] != undefined) {
                $('#post_type').val(file_contents['type'])
            }
            //post status (select dropdown)
            if(file_contents['status'] != undefined) {
                $('#post_status').val(file_contents['status'])
            }
            //author (select dropdown)
            if(file_contents['author'] != undefined) {
                $('#post_author').val(file_contents['author'])
            }
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

})