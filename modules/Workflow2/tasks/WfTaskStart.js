/**
 * Created by Stefan on 07.11.2015.
 */
jQuery(function() {
    jQuery('#runtimeSelection').on('change', function() {
        var option = jQuery('option:selected', this);

        if(option.data('description') != '') {
            jQuery('#triggerDescription').text(option.data('description'));
        } else {
            jQuery('#triggerDescription').text('');
        }
    });
    jQuery('#runtimeSelection').trigger('change');

    opener.setWorkflowTrigger(jQuery('#runtimeSelection').val());
});

