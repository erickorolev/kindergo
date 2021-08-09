// Copyright: Base on code from VtigerCRM Developers from Internal Workflow Module
var CreateEventEditor = {
    registerEvents:function() {

        jQuery('input[name="task[recurring]"]').on('toggle.rcSwitcher', function(e) {
            CreateEventEditor.showRepeatUI();
        });

        jQuery('#recurringType').on('change', function(e) {
            var currentTarget = jQuery(e.currentTarget);
            var recurringType = currentTarget.val();
            CreateEventEditor.changeRecurringTypesUIStyles(recurringType);

        });

        jQuery('input[name="task[repeat][repeatMonth]"]').on('change', function(e) {
            //If repeatDay radio button is checked then only select2 elements will be enable
            CreateEventEditor.repeatMonthOptionsChangeHandling();
        });

        CreateEventEditor.repeatMonthOptionsChangeHandling();
    },
    changeRecurringTypesUIStyles : function(recurringType) {
        if(recurringType == 'Daily' || recurringType == 'Yearly') {
            jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
            jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
        } else if(recurringType == 'Weekly') {
            jQuery('#repeatWeekUI').removeClass('hide').addClass('show');
            jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
        } else if(recurringType == 'Monthly') {
            jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
            jQuery('#repeatMonthUI').removeClass('hide').addClass('show');
        }
    },
    repeatMonthOptionsChangeHandling : function() {
        //If repeatDay radio button is checked then only select2 elements will be enable
        if(jQuery('#repeatDay').is(':checked')) {
            jQuery('#repeatMonthDate').attr('disabled', true);
            jQuery('#repeatMonthDayType').select2("enable");
            jQuery('#repeatMonthDay').select2("enable");
        } else {
            jQuery('#repeatMonthDate').removeAttr('disabled');
            jQuery('#repeatMonthDayType').select2("disable");
            jQuery('#repeatMonthDay').select2("disable");
        }
    },
    showRepeatUI: function() {
        var repeatUI = jQuery('#repeatUI');

        var element = jQuery('input[name="task[recurring]"]');

        if(element.is(':checked')) {
            repeatUI.slideDown();
        } else {
            repeatUI.slideUp();
        }

    }
};
jQuery(function() {
    CreateEventEditor.registerEvents();
});