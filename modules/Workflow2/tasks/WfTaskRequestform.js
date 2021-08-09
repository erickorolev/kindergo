(function($) {
    var FormBuilder = function(designerEle, settingsEle) {
        this.designerEle = $(designerEle);
        this.settingsEle = $(settingsEle);

        this.Settings = {
            'headline': 'This process need some information',
            'width':    '550px',
            'scope':    'value',
            'continuetext': 'Execute process',
            'stoptext': 'Stop process'
        };

        this.disableRedraw = false;
        this.valueGetter = {};
        this.valueSetter = {};
        this.valueInit = {};
        this.currentField = null;
        this.fieldTypes = {};
        this.fieldMaxCounter = 0;

        this.load = function(existingConfig) {
            this.delayRedraw(true);
            if(typeof existingConfig.rows !== 'undefined') {
                $.each(existingConfig.rows, $.proxy(function (index, row) {
                    var rowEle = this.addRow();

                    $.each(row.fields, $.proxy(function (index, data) {
                        var fieldEle = this.addField(rowEle);

                        fieldEle.data('config', $.parseJSON(data));

                        //console.log(fieldEle.data('config'));
                    }, this));
                }, this));
            }

            if(typeof existingConfig.settings !== 'undefined') {
                this.Settings = $.parseJSON(existingConfig.settings);
            }
            this.delayRedraw(false);
            this.redrawForm();
        };

        this.setAvailableFieldTypes = function(fieldTypes) {
            this.fieldTypes = fieldTypes;
        };

        this.delayRedraw = function(shouldDelayRedraw) {
            this.disableRedraw = shouldDelayRedraw;
        };

        this.getSettingsValueFromField = function(key) {
            if(typeof this.valueGetter[key] === 'undefined') {
                return $('.ConfigValue[data-key="'  + key + '"]', this.settingsEle).val();
            }

            return this.valueGetter[key](key);
        };
        this.setSettingsValueToField = function(key, value) {
            if(typeof this.valueSetter[key] === 'undefined') {
                return $('.ConfigValue[data-key="'  + key + '"]', this.settingsEle).val(value);
            }

            return this.valueSetter[key](key, value);
        };

        this.registerInit = function(key, callback) {
            this.valueInit[key] = callback;
        };

        this.registerFieldValueGetter = function(key, callback) {
            this.valueGetter[key] = callback;
        };
        this.registerFieldValueSetter = function(key, callback) {
            this.valueSetter[key] = callback;
        };

        this.setFieldConfig = function(key, value) {
            var config = this.currentField.data('config');

            config[key] = value;

            this.currentField.data('config', config);
        };

        this.init = function() {
            this.designerEle.sortable({
                'handle':'.RowMoveHandler',
                'axis':'y',
                stop:$.proxy(function() {
                    this.redrawForm();
                }, this)
            });

            initMaterialEvents('#FormSettingsTab');
            initMaterialEvents('#FieldSettingsTab');

            $('.DeleteFieldBtn').on('click', $.proxy(function(e) {
                var text = $(e.currentTarget).data('text');

                bootbox.confirm(text, $.proxy(function(response) {
                    if(response === false) return;

                    this.currentField.remove();
                    this.redrawForm();
                }, this));
            }, this));

            $('.ConfigHeadlineValue .textfield').on('change', $.proxy(function(e) {
                this.Settings['headline'] = $(e.currentTarget).val();
                this.redrawForm();
            }, this)).val(this.Settings['headline']).trigger('blur');

            $('.ConfigWidthValue .textfield').on('change', $.proxy(function(e) {
                this.Settings['width'] = $(e.currentTarget).val();
                this.redrawForm();
            }, this)).val(this.Settings['width']).trigger('blur');

            $('.ConfigScopeValue .textfield').on('change', $.proxy(function(e) {
                this.Settings['scope'] = $(e.currentTarget).val();
                this.redrawForm();
            }, this)).val(this.Settings['scope']).trigger('blur');

            $('.ConfigContinueText .textfield').on('change', $.proxy(function(e) {
                this.Settings['continuetext'] = $(e.currentTarget).val();
                this.redrawForm();
            }, this)).val(this.Settings['continuetext']).trigger('blur');

            $('.ConfigStopText .textfield').on('change', $.proxy(function(e) {
                this.Settings['stoptext'] = $(e.currentTarget).val();
                this.redrawForm();
            }, this)).val(this.Settings['stoptext']).trigger('blur');

            $('.ConfigTypeValue').on('change', $.proxy(function(e) {
                this.setFieldConfig('type', $(e.currentTarget).val());
                this.redrawForm();
                this.currentField.trigger('click');
            }, this));
            $('.ConfigLabelValue .textfield').on('change', $.proxy(function(e) {
                this.setFieldConfig('label', $(e.currentTarget).val());
                this.redrawForm();

            }, this));
            $('.ConfigNameValue .textfield').on('change', $.proxy(function(e) {
                this.setFieldConfig('name', $(e.currentTarget).val());
                this.redrawForm();

            }, this));

            $('.FormSettingsBtn').on('click', function() {
                $('.FormBuilderTabs').hide();
                $('.FormBuilderTabs#FormSettingsTab').show();
            });

            this.settingsEle.on('change', '.group', $.proxy(function(e) {
                var ele = $(e.currentTarget);

                fieldValue = this.getSettingsValueFromField(ele.data('key'));

                this.setFieldConfig(ele.data('key'), fieldValue);

                this.redrawForm();
            }, this));


            this.designerEle.on('click', '.FormBuilderFieldContainer', $.proxy(function(e) {
                var target = $(e.target);

                $('textarea:focus, input:focus, select:focus').blur();
                window.setTimeout($.proxy(function() {
                    if(target.hasClass('FormBuilderFieldContainer') == false) target = target.closest('.FormBuilderFieldContainer');

                    this.currentField = target;

                    $('.FormBuilderTabs').hide();
                    $('.FormBuilderTabs#FieldSettingsTab').show();

                    $('.ActiveField').removeClass('ActiveField');
                    target.addClass('ActiveField');

                    var config = target.data('config');

                    var type = config.type;
                    var html = $('#fieldtemplate_' + type).html();

                    html = html.replace(/&gt;script/g, '<script', html);
                    html = html.replace(/&gt;\/script/g, '</script', html);
                    this.valueInit = {};

                    this.settingsEle.html(html);

                    createTemplateFields(this.settingsEle);

                    jQuery('.MakeSelect2', this.settingsEle).removeClass('MakeSelect2').select2();

                    $('.ConfigTypeValue').select2('val', config.type);
                    $('.ConfigLabelValue .textfield').val(config.label).trigger('blur');
                    $('.ConfigNameValue .textfield').val(config.name).trigger('blur');

                    $.each(this.valueInit, function(key, callback) {
                        callback(key);
                    });

                    initMaterialEvents(this.settingsEle);

                    $('.group', this.settingsEle).each($.proxy(function(index, ele) {
                        var key = $(ele).data('key');

                        if(typeof config[key] !== 'undefined') {
                            // console.lo

                            this.setSettingsValueToField(key, config[key]);
                        }
                    }, this));

                    jQuery(this.settingsEle).trigger('InitComponents');
                }, this), 50);
            }, this));

        };

        this.getGroup = function(key) {
            return $('.group[data-key="' + key + '"]');
        };

        this.redrawForm = function() {
            if(this.disableRedraw === true) return;

            var html = '';
            var Scope = this.Settings.scope;

            var AvailableFieldTypes = this.fieldTypes;
            var container = $('#FormDesignContainer');
            if(Scope === '') {
                var fieldPrefix = '$env["';
            } else {
                var fieldPrefix = '$env["' + Scope + '"]["';
            }

            $('.FormBuilderOuterRow', container).each(function(rowIndex, row) {

                $('.FormBuilderFieldContainer', row).each(function(fieldIndex, field) {
                    var field = $(field);
                    field.find('.FieldType').html(AvailableFieldTypes[field.data('config').type]);
                    field.find('.FieldLabel').html(field.data('config').label);

                    field.find('.VarName').html(fieldPrefix + field.data('config').name + '"]');

                    html += '<input type="hidden" name="task[form][rows]['+rowIndex+'][fields][]" value="' + htmlEntities(JSON.stringify(field.data('config'))) + '" />';
                });

            });

            html += '<input type="hidden" name="task[form][settings]" value="' + htmlEntities(JSON.stringify(this.Settings)) + '" />';

            $('#HiddenFormData').html(html);
        };

        this.addField = function(rowEle) {
            if(rowEle.hasClass('FormBuilderRowContainer') === false) {
                rowEle = rowEle.find('.FormBuilderRowContainer');
            }

            var fieldId = 'row_' + Math.floor(Math.random() * 100000);
            rowEle.append('<div class="FormBuilderFieldContainer" id="' + fieldId + '"><div class="pull-right ExtraInformation"><span class="FieldLabel"></span><br/><span class="VarName"></span></div><br/><span class="FieldType"></span></div>');

            var count = this.fieldMaxCounter + 1;
            $('#' + fieldId).data('config', {'type':'text', 'label':'Field ' + count,'name':'field' + count});
            this.redrawForm();

            this.fieldMaxCounter++;
            return $('#' + fieldId);
        };

        this.addRow = function() {
            var containerId = 'row_' + Math.floor(Math.random() * 100000);
            var ele = '<div id="' + containerId + '" class="FormBuilderOuterRow"><div class="RowActions pull-right"><i class="fa fa-arrows RowMoveHandler" aria-hidden="true"></i><br/><!--<i class="fa fa-cog RowConfig" aria-hidden="true"></i>--><br/><i class="fa fa-plus-square addFieldBtn" aria-hidden="true"></i></div><div class="FormBuilderRowContainer"></div></div>';

            this.designerEle.append(ele);

            $('.addFieldBtn').off('click').on('click', $.proxy(function(e) {
                var fieldParent = $(e.currentTarget).closest('.FormBuilderOuterRow').find('.FormBuilderRowContainer');

                this.addField(fieldParent);
            }, this));

            $( "#" + containerId + ' .FormBuilderRowContainer' ).sortable({
                connectWith: ".FormBuilderRowContainer",
                dropOnEmpty:true,
                distance:30,
                stop:$.proxy(function() {
                    this.redrawForm();
                }, this)
            }).disableSelection();

            this.redrawForm();

            return  $( "#" + containerId );
        };

        this.initEvents = function() {

        }
    };

    $(function() {
        window.setTimeout(function() {
            var formBuilderObj = new FormBuilder('#FormDesignContainer', '#FormSettingsContainer');
            formBuilderObj.setAvailableFieldTypes(AvailableFieldTypes);

            if(typeof oldTask == 'object' && oldTask !== null && typeof oldTask.form != 'undefined') {
                formBuilderObj.load(oldTask.form);
            }

            formBuilderObj.init();

            window.FormBuilder = formBuilderObj;
        }, 250);
        //

        $('.addRowBtn').on('click', function() {
            window.FormBuilder.addRow();
        });


    });

})(jQuery);