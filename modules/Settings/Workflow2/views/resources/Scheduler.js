var Scheduler = {
    updateCounter:0,
    newScheduler: function() {
        jQuery.post('index.php', {module:'Workflow2', parent:'Settings', action:'SchedulerAdd'}, function() {
            Scheduler.refreshList();
        });
    },
    delScheduler: function(id) {
        if(!confirm('Really delete?')) return;

        jQuery.post('index.php', { module:'Workflow2', parent:'Settings', action:'SchedulerDel', scheduleId: id }, function() {
            jQuery('.cronRow_' + id).slideUp();
        });

    },
    refreshList: function() {
        RedooUtils('Workflow2').refreshContent('SettingsScheduler', true, {}, true).then(Scheduler.initEvent);
    },
    initEvent: function() {
        jQuery('.RecordChooseBtn').on('click', function() {
            window.workflowID = jQuery('#workflowSelection_' + jQuery(this).data('sid') + ' option:selected').val();
            if(window.workflowID == 0) return;
            window.workflowModuleName = jQuery('#workflowSelection_' + jQuery(this).data('sid') + ' option:selected').data('module');
            window.dateFormat = 'Y-m-d';

            ConditionPopup.open(
                '#records_' + jQuery(this).data('sid'),
                jQuery('#workflowSelection_' + jQuery(this).data('sid') + ' option:selected').data('module'),
                'Choose Records',
                {'calculator':true});
        });

        jQuery('.CronPreset').bind('change', function(event) {
            var target = jQuery(event.target);
            var value = target.val();
            if(value == '') return;

            var parts = value.split(';');
            var parent = target.closest('.cronRow');

            jQuery('[data-field="minute"]', parent).val(parts[0]).trigger('change');
            jQuery('[data-field="hour"]', parent).val(parts[1]).trigger('change');
            jQuery('[data-field="dom"]', parent).val(parts[2]).trigger('change');
            jQuery('[data-field="month"]', parent).val(parts[3]).trigger('change');
            jQuery('[data-field="dow"]', parent).val(parts[4]).trigger('change');
            jQuery('[data-field="year"]', parent).val(parts[5]).trigger('change');
1
        });

        jQuery('.cronRow').on('update.nextexecution', function(e) {
            var id = jQuery(e.currentTarget).data('id');
            var CurrentUpdateIndex = Scheduler.updateCounter;
            Scheduler.updateCounter++;

            RedooAjax('Workflow2').postSettingsAction('PlanerNextExecution', { id: id, index:CurrentUpdateIndex }, 'json').then(function(response) {
                if(response.index != CurrentUpdateIndex) return;

                console.log(jQuery('.cronRow_'+id+'.SecondSchedulerRow .NextExecutionTimer'));
                jQuery('.cronRow_'+id+'.SecondSchedulerRow .NextExecutionTimer').html(response.execution);
            });
        });
        jQuery('.cronRow input, .cronRow select').bind('change', function(event) {
            var target = jQuery(event.target);

            var field = target.data('field');
            if(typeof field !== 'undefined') {

                target.attr('disabled', 'disabled');
                if(target.val() == '' && target.data('field') != 'condition') {
                    target.val('*')
                }
                var value = target.val();

                if(target.attr('type') == 'checkbox') {
                    if(target.prop('checked') != true) {
                        value = 0;
                    }
                }
                if(target.data('field') == 'workflow_id') {
                    // console.log('#records_' + target.data('sid'));
                    jQuery('#records_' + target.data('sid')).val('').trigger('change');
                }


                jQuery.post('index.php', {
                    module: 'Workflow2',
                    parent: 'Settings',
                    action: 'SchedulerUpdate',
                    scheduleId: target.data('sid'),
                    field: field,
                    value: value
                }, function () {
                    target.removeAttr('disabled');

                    jQuery('.cronRow_' + target.data('sid') + '.FirstSchedulerRow').trigger('update.nextexecution');
                });
            }

        });

    }

};
jQuery(function() {

    Scheduler.initEvent();

});

