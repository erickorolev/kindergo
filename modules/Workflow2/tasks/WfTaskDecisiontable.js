(function($) {
    $('.AddRowBtn').on('click', function(e) {
        Decisiontable.addRow();
    });
    $('.AddDecisionBtn').on('click', function(e) {
        Decisiontable.addDecision();
    });
    $('.AddSetterBtn').on('click', function(e) {
        Decisiontable.addSetter();
    });

    window.Decisiontable = {
        _ColType: {
            'decision': [],
            'setter':[]
        },
        _Columns:{},
        _ColCounter:0,
        _Data:[],
        _DelayDraw:false,
        addEvents:function() {
            $('.RemoveRow').on('click', function(e) {
                var target = $(e.currentTarget);

                var row = target.closest('.RowAction');
                var rowid = row.data('rowid');

                Decisiontable._Data[rowid] = undefined;
                row.closest('tr').remove();
            });

            $('.form-control').on('focus', function(e) {
                $(e.currentTarget).closest('tr').addClass('Focused');
            });
            $('.form-control').on('blur', function(e) {
                $(e.currentTarget).closest('tr').removeClass('Focused');
            });

            $('.RemoveColumn').on('click', function(e) {
                var target = $(e.currentTarget);

                var col = target.closest('.HeadAction');
                var rowid = col.data('columnid');
                var type = col.data('type');

                Decisiontable._Columns[rowid] = undefined;

                $.each(Decisiontable._ColType[type], function(index, ele) {
                    if(ele == rowid) {
                        Decisiontable._ColType[type][index] = undefined;
                        return false;
                    }
                });

                if($('#th_' + type).attr('colspan') == '1') {
                    if(type == 'setter') {
                        Decisiontable.addSetter();
                    } else {
                        Decisiontable.addDecision();
                    }
                }

                $('#th_' + type).attr('colspan', $('#th_' + type).attr('colspan') - 1);

                var totalColNumber = Number($('#th_decision').attr('colspan')) + Number($('#th_setter').attr('colspan')) + 1;
                $('#th_decision').css('width', ((100 / totalColNumber) * (Number($('#th_decision').attr('colspan')) + 1)) + '%');
                $('#th_setter').css('width', ((100 / totalColNumber) * $('#th_setter').attr('colspan')) + '%');

                Decisiontable.redrawTable();
            });

            $('.SetterTypeSelect').on('change', function(e) {
                var target = $(e.currentTarget);

                var type = target.val();
                var parent = target.parent();
                var currentValue = $('select.SetterOptionValue, input.SetterOptionValue', parent).val();
                var currentName = $('select.SetterOptionValue, input.SetterOptionValue', parent).attr('name');

                var html = Decisiontable.getSetterInput(type, currentName, currentValue);

                $('.SetterOptionContainer', parent).html(html);
            });
        },
        getSetterInput:function(type, inputName, currentValue) {
            if(type == '' || typeof type == 'undefined') type = 'envvar';

            switch(type) {
                case 'envvar':
                    var html = '<input type="text" class="SetterOptionValue form-control" placeholder=\'$env["..."]\' name=' + inputName + '" value="' + htmlEntities(currentValue) + '" />';
                    break;
                case 'field':
                    var html = '<select class="SetterOptionValue MakeSelect2 form-control" name="' + inputName + '">';

                    $.each(ModuleFields, function(blockLabel, fieldList) {
                        html += '<optgroup label="' + blockLabel + '">';
                        $.each(fieldList, function(index, field) {
                            html += '<option value="' + field.name + '" ' + (currentValue == field.name ? 'selected="selected"':'') +'>' + field.label + '</option>';
                        });
                        html += '</optgroup>';
                    });
                    html += '</select>';
                    break;
            }

            return html;
        },
        delayRedraw:function(value) {
            Decisiontable._DelayDraw = value;
        },
        redrawTable:function() {
            if(Decisiontable._DelayDraw === true) {
                return;
            }

            var structureHTML = '';
            $('.DecisionTableStructure').remove();

            var headActions = '<tr><td></td>';
            var head = '<tr class="DecisionHead"><td></td>';
            var count = {'decision':0,'setter':0};
            var ColCounter = 0;
            $.each(Decisiontable._ColType.decision, function(index, data) {
                if(typeof data == 'undefined') return;
                count.decision++;
                structureHTML += '<input type="hidden" class="DecisionTableStructure" name="task[structure][_ColType][decision][' + (ColCounter++) + ']" value="' + data + '" />';
                headActions += '<td data-type="decision" data-columnid="' + data + '" class="HeadAction"><i style="cursor:pointer;" class="fa fa-trash RemoveColumn" aria-hidden="true"></i></td>';
                head += '<td style="vertical-align: bottom;">';
                head += '<div class="insertTextfield" style="display: inline-block;width:100%;" data-name="task[structure][_Columns][' + data + '][value]" data-id="data_structure_' + data + '_value">' + Decisiontable._Columns[data].value + '</div>';
                head += '</td>';
            });

            var ColCounter = 0;
            $.each(Decisiontable._ColType.setter, function(index, data) {
                if(typeof data == 'undefined') return;
                count.setter++;
                structureHTML += '<input type="hidden" class="DecisionTableStructure" name="task[structure][_ColType][setter][' + (ColCounter++) + ']" value="' + data + '" />';
                headActions += '<td data-type="setter" data-columnid="' + data + '" class="HeadAction"><i style="cursor:pointer;"  class="fa fa-trash RemoveColumn" aria-hidden="true"></i></td>';
                head += '<td style="vertical-align: bottom;"><select class="SetterTypeSelect" style="width:100%;height:28px;" name="task[structure][_Columns][' + data + '][type]"><option value="envvar">$env Variable</option><option value="field" ' + (Decisiontable._Columns[data].type == 'field'?'selected="selected"':'') + '>Fieldvalue</option></select><div class="SetterOptionContainer">' + Decisiontable.getSetterInput(Decisiontable._Columns[data].type, 'task[structure][_Columns][' + data + '][value]', Decisiontable._Columns[data].value) + '</div></div>';


            });
            head += '</tr>';
            headActions += '</tr>';

            html = headActions;
            html += head;

            $.each(Decisiontable._Data, function(rowIndex, rowData) {
                if(typeof rowData == 'undefined') return;
                var rowHTML = '<tr class="TableRowTR">';
                    rowHTML += '<td data-rowid="' + rowIndex + '" class="RowAction"><i style="cursor:pointer;"  class="fa fa-trash RemoveRow" aria-hidden="true"></i></td>';
                    $.each(Decisiontable._ColType.decision, function(colIndex, colData) {
                        if(typeof colData == 'undefined') return;
                        if(typeof rowData[colData] == 'undefined') rowData[colData] = {value:'',type:'equal'};

                        rowHTML += '<td><div  style="display:flex;">';
                        rowHTML += '<select name="task[data]['+rowIndex+']['  + colData + '][type]" style="font-size:10px;"><option value="equal">equal</option><option value="contain" ' + (rowData[colData].type == 'contain'?'selected="selected"':'') + ' >contains</option><option value="expression" ' + (rowData[colData].type == 'expression'?'selected="selected"':'') + ' >expression</option></select>';
                        rowHTML += '<input type="text" class="form-control" name="task[data]['+rowIndex+']['  + colData + '][value]" value="' + htmlEntities(rowData[colData].value) + '" />';
                        rowHTML += '</div></td>';
                    });

                    $.each(Decisiontable._ColType.setter, function(colIndex, colData) {
                        if(typeof colData == 'undefined') return;
                        if(typeof rowData[colData] == 'undefined') rowData[colData] = {value:'',type:'envvar'};

                        rowHTML += '<td>';
                        rowHTML += '<div class="insertTextfield" style="display: inline-block;width:100%;" data-name="task[data]['+rowIndex+']['  + colData + '][value]" data-id="data_row_' + rowIndex + '_value' + colData + '">' + htmlEntities(rowData[colData].value) + '</div>';
                        rowHTML += '</td>';
                    });
                rowHTML += '</tr>';

                html += rowHTML;
            });


            $('#mainTaskForm').append(structureHTML);

            $('#th_decision').attr('colspan', count.decision);
            $('#th_setter').attr('colspan', count.setter);

            var totalColNumber = Number($('#th_decision').attr('colspan')) + Number($('#th_setter').attr('colspan')) + 1;
            $('#th_decision').css('width', ((100 / totalColNumber) * (Number($('#th_decision').attr('colspan')) + 1)) + '%');
            $('#th_setter').css('width', ((100 / totalColNumber) * $('#th_setter').attr('colspan')) + '%')

            $('#DecisionTable tbody').html(html);
            createTemplateFields('#DecisionTable');

            $('.MakeSelect2').each(function(index, ele) {
                $(ele).removeClass('MakeSelect2');
                $(ele).select2();
            });
            Decisiontable.addEvents();
        },
        addRow:function() {
            var row = {};

            $.each(Decisiontable._Columns, function(index, data) {
                row[data] = '';
            });

            Decisiontable._Data.push(row);

            Decisiontable.redrawTable();
        },
        addDecision:function() {
            do {
                Decisiontable._ColCounter++;
                key = 'col_' + Decisiontable._ColCounter;
            } while(typeof Decisiontable._Columns[key] != 'undefined');

            Decisiontable._Columns[key] = {
                'value': ''
            };

            Decisiontable._ColType['decision'].push(key);
            Decisiontable.redrawTable();
        },
        addSetter:function() {
            do {
                Decisiontable._ColCounter++;
                key = 'col_' + Decisiontable._ColCounter;
            } while(typeof Decisiontable._Columns[key] != 'undefined');

            Decisiontable._Columns[key] = {
                'value': ''
            };

            Decisiontable._ColType['setter'].push(key);
            Decisiontable.redrawTable();
        },

        init:function(structure, data) {
            if(typeof structure._Columns != 'undefined') {
                Decisiontable._Columns = structure._Columns;
            }
            if(typeof structure._ColType != 'undefined') {
                Decisiontable._ColType = structure._ColType;
            }

            Decisiontable.delayRedraw(true);
            if(Decisiontable._ColType.decision.length == 0) {
                Decisiontable.addDecision();
            }
            if(Decisiontable._ColType.setter.length == 0) {
                Decisiontable.addSetter();
            }
            if(Decisiontable._Data.length == 0) {
                Decisiontable.addRow();
            }

            Decisiontable._Data = [];
            $.each(data, function(index, data) {
                Decisiontable._Data.push(data);
            });


            Decisiontable._ColCounter = Object.keys(Decisiontable._Columns).length+ 1;

            Decisiontable.delayRedraw(false);

            Decisiontable.redrawTable();

            $.each(data, function(rowIndex, rowData) {
                //var parent =
            });
        }
    };
})(jQuery);