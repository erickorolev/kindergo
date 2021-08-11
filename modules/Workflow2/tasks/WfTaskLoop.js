jQuery(function() {
    jQuery('#EnableExpression').on('toggle.rcSwitcher', function(e) {
        ShowOnExpression();
    });

    jQuery('#EnableWorkflow').on('toggle.rcSwitcher', function(e) {
        ShowOnWorkflow();
    });

    function ShowOnExpression() {
        var checked = jQuery('#EnableExpression').prop('checked');

        if(checked === true) {
            jQuery('.ShowOnExpression').slideDown('fast');
        } else {
            jQuery('.ShowOnExpression').slideUp('fast');
        }
    }
    function ShowOnWorkflow() {
        var checked = jQuery('#EnableWorkflow').prop('checked');

        if(checked === true) {
            jQuery('.ShowOnWorkflow').slideDown('fast');
        } else {
            jQuery('.ShowOnWorkflow').slideUp('fast');
        }
    }

    ShowOnExpression();
    ShowOnWorkflow();
});