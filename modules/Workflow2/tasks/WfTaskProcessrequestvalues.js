jQuery(function() {
    jQuery('.headLineBlock').on('click', function() {
        console.log(jQuery(this).parent().find('input[type="checkbox"]'));
        jQuery(this).parent().parent().find('input[type="checkbox"]').prop('checked', jQuery(this).prop('checked'));
    });
});