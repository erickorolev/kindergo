/**
 * Created by Stefan on 28.06.2017.
 */
(function($) {
    $(function() {
        $('.createMailscannerConfig').on('click', function() {
            RedooAjax('Workflow2').postAction('MailscannerCreate', {}, true, 'json').then(function(response) {
                window.location.href = 'index.php?module=Workflow2&view=MailscannerEditor&scanner=' + response.id + '&parent=Settings';
            })
        });

        $('.ShowHistory').on('click', function(e) {
            e.stopPropagation();
            var scannerid = $(e.currentTarget).closest('.MailScannerConfiguration').data('id');

            RedooAjax.postView('MailscannerHistory', {scannerid:scannerid, page:1}, true).then(function(response) {
                app.showModalWindow(response);
            });
        });

        /** Editor **/
        $('.addCondition').on('click', function() {
            addCondition();
        });
        $('.DeleteMailscanner').on('click', function() {
            bootbox.confirm("Please confirm to delete this mailscanner configuration", function(markdone){
                if(markdone == false) return;

                RedooAjax('Workflow2').postAction('MailscannerDelete', {scannerid:ScannerId, }, true).then(function() {
                    window.location.href = "index.php?module=Workflow2&view=Mailscanner&parent=Settings";
                })
            });
        });

        $('.addEnvVar').on('click', function() {
            var type = $('#envvar').val();
            addEnvVar(type);
        });

        $('.ExecuteNow').on('click', function() {
            bootbox.prompt({
                title: "How much mails do you want to process?",
                value: 5,
                buttons: {
                    confirm: {
                        label: 'Next',
                        className: 'btn-success'
                    },
                    cancel: {
                        label: 'Cancel',
                        className: 'btn-danger'
                    }
                },
                callback:function(mailnumber){
                    if(mailnumber == null) return;

                    bootbox.confirm("Do you want to mark processed mails as done?", function(markdone){

                        $('#executeResult').html('<em style="font-size:12px;">Execute mailscanner ...</em>');
                        RedooAjax('Workflow2').postAction('MailscannerExecute', { scannerid:ScannerId, mailnumber:mailnumber, markdone:markdone }, true, 'json').then(function(response) {
                            if(response.counter == 1) {
                                $('#executeResult').html('<span style="font-size:12px;">' + response.counter + ' Mail processed</span>');
                            } else {
                                $('#executeResult').html('<span style="font-size:12px;">' + response.counter + ' Mails processed</span>');
                            }
                        });
                    });
                }
            });
        });

        $('.RemoveProcessedMail').on('click', function(e) {
            var row = $(e.currentTarget).closest('tr');
            var processedId = row.data('id');

            RedooAjax('Workflow2').postAction('MailScannerRemoveProcessed', {scannerid:ScannerId, processedid:processedId}, true, 'json').then(function() {
                row.remove();
            });
        });

        $('.MailscannerExecuteAgain').on('click', function(e) {
            bootbox.confirm('Please confirm to execute this mail again!', function(result) {
                if(result === false) return;

                var row = $(e.currentTarget).closest('tr');
                var processedId = row.data('id');

                RedooAjax('Workflow2').postAction('MailScannerExecuteAgain', {scannerid:ScannerId, processedid:processedId}, true, 'json').then(function() {
                    row.remove();
                });
            });
        });

        $('.TestMSConfiguration').on('click', function() {
            $('#MS_TestResult').html('<h4>eMails will be loaded ...</h4>');
            RedooAjax('Workflow2').postAction('MailscannerTest', {scannerid:ScannerId}, true, 'json').then(function(response) {

                if(response.mails.length === 0) {
                    var html = '<p class="alert alert-warn">No mails match this condition / folderlist</p>';
                } else {
                    var html = '<table class="table table-condensed">';
                    html += '<tr>';
                        html += '<th style="width:200px;">Date</th>';
                        html += '<th style="width:200px;">From</th>';
                        html += '<th style="width:100px;">Size</th>';
                        html += '<th>Subject</th>';
                    html += '</tr>';

                    $.each(response.mails, function(index, mail) {
                        html += '<tr>';
                            html += '<td>' + mail.date + '</td>';
                            html += '<td>' + mail.from + '</td>';
                            html += '<td>' + mail.size + '</td>';
                            html += '<td><strong>' + mail.subject + '</strong></td>';
                        html += '</tr>';
                    });
                    html += '</table>';
                }

                $('#MS_TestResult').html(html);
            });
        });
    });

    var MailScanner = {
        initEnvironment:function(environment) {
            $.each(environment, function(index, value) {
                var parent = addEnvVar(value['type']);

                $('.VarValue', parent).val(value.envvar);
            });
        },
        initConditions:function(conditions) {
            var container = $('#Conditions');

            if(conditions.length == 0) {
                container.html(container.data('emptytext')).data('isempty', '1');
            } else {
                $.each(conditions, function(index, value) {
                    var parent = addCondition();

                    $('.Target', parent).val(value.field).trigger('change');
                    if(typeof value.parameter == 'string') {
                        $('.MSParameter', parent).val(value.parameter.replace(/&#039;/g, "'"));
                    }
                });
            }
        }
    };

    var envCounter = 0;

    function addEnvVar(type) {
        var typetext = $('#envvar option[value="'+type +'"]').text();

        var html = '<tr id="EnvTR_' + envCounter + '">';
            html += '<td><i class="icon-remove RemoveEnvVar" style="margin-bottom:-3px;"></i>&nbsp;&nbsp;<input type="hidden" class="VarType" name="environment[' + envCounter + '][type]" value="' + type + '" />' + typetext + '</td>';
            html += '<td>$env[<input type="text" class="VarValue" style="border:1px solid #ccc;margin:0 2px;box-shadow:none;" name="environment[' + envCounter + '][envvar]" value="" />]</td>';
        html += '</tr>';

        $('#envVarTable').append(html).show();

        var retVal = $('#EnvTR_' + envCounter + '');
        envCounter++;

        $('.RemoveEnvVar', retVal).on('click', function(e) {
            $(e.currentTarget).closest('tr').remove();
        });
        return retVal;
    }

    var condCounter = 0;
    function addCondition() {
        var container = $('#Conditions');
        var counter = condCounter;
        condCounter++;

        var html = $('.MS_Condition_Template').html();

        html = html.replace(/##INDEX##/g, counter);

        var id = 'condition_' + counter;

        if(container.data('isempty') == '1') {
            container.html(html);
            container.data('isempty', '0');
        } else {
            container.append(html);
        }

        refreshParameters($('#' + id));

        $('.Target','#' + id).on('change', function(e) {
            refreshParameters($(e.currentTarget).closest('.MS_Condition'));
        });
        $('.DeleteCondition', '#' + id).on('click', function(e) {
            $(e.currentTarget).closest('.MS_Condition').remove();
        });

        $('.Target.InitSelect2').select2();
        $('.Target.InitSelect2').removeClass('InitSelect2');

        return $('#' + id);
    }

    function refreshParameters(container) {
        var index = container.data('index');
        var target = $('.Target option:selected', container);
        if($('#parameter_' + index + '').length > 0) {
            var oldValue = $('#parameter_' + index + '').val();
        } else {
            oldValue = '';
        }

        var html = '';
        if(target.data('type') == 'text') {
            var html = '<input type="text" class="MSParameter defaultTextfield" id="parameter_' + index + '" name="condition[' + index + '][parameter]" value="' + oldValue + '" />';
        }
        if(target.data('type') == 'date') {
            var html = '<input type="date" class="MSParameter defaultTextfield" id="parameter_' + index + '" name="condition[' + index + '][parameter]" value="' + oldValue + '" />';
        }

        $('.SearchParameter', container).html(html);
    }

    window.MailScanner = MailScanner;
})(jQuery);