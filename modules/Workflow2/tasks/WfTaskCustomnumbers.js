jQuery('body').on('inputFieldsReady', function() {

    jQuery('#serieField').on('input', function(e) {
        var value = jQuery(this).val();

        if (typeof seriesData[value] === 'undefined') {
            jQuery('#serie_length').val('');
            jQuery('#serie_start').val('');
            jQuery('#serie_prefix').val('');
            jQuery('#serie_length').removeAttr('readonly');
            jQuery('#serie_prefix').removeAttr('readonly');

            return;
        }

        jQuery('#serie_length').val(seriesData[value].length);
        jQuery('#serie_start').val(seriesData[value].current);
        jQuery('#serie_prefix').val(seriesData[value].prefix);

        if (jQuery('#serie_prefix').val().indexOf('$') === -1) {
            jQuery('#serie_length').attr('readonly', 'readonly');
            jQuery('#serie_prefix').attr('readonly', 'readonly');
        }
    });

    var value = jQuery('#serieField').val();

    if(typeof seriesData[value] !== 'undefined') {
        if(jQuery('#serie_prefix').val().indexOf('$') === -1) {
            jQuery('#serie_length').val(seriesData[value].length);
            jQuery('#serie_start').val(seriesData[value].current);
            jQuery('#serie_prefix').val(seriesData[value].prefix);

            jQuery('#serie_length').attr('readonly', 'readonly');
            jQuery('#serie_prefix').attr('readonly', 'readonly');
        }

    }




});