jQuery(function() {
    var content = ''
    jQuery('.NotHTMLFormated').on('click', function() {
        if(jQuery(this).prop('checked')) {
            //console.log(jQuery('#PreviewContainer').html(), jQuery('#PreviewContainer').html().replace('<','&gt;'));
            content = jQuery('#PreviewContainer').html();

            jQuery('#PreviewContainer').text(jQuery('#PreviewContainer').html());
            jQuery('#PreviewContainer').css('white-space','pre');
        } else {
            jQuery('#PreviewContainer').css('white-space', 'inherit');
            jQuery('#PreviewContainer').html(content);
        }
    });
});