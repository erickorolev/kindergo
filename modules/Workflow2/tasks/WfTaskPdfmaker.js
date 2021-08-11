jQuery(function() {
    if(typeof templateList != 'undefined') {
        var val = jQuery("#TemplateIDList").val();
        jQuery("#TemplateIDList").select2({
            multiple:true,
            width:'100%',
            initSelection: function (element, callback) {
                var parts = val.split(',');

                var select = [];
                jQuery.each(parts, function(index, value) {
                    select.push({ id:value, text: templateData[value] })
                });
                callback(select);
            },
            data: templateList
        }).select2("container").find("ul.select2-choices").sortable({
            containment: 'parent',
            start: function() { jQuery("#TemplateIDList").select2("onSortStart"); },
            update: function() { jQuery("#TemplateIDList").select2("onSortEnd"); }
        });
    }
});