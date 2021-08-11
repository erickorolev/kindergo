window.closePopup = window.close;

function removeBlock(block_id) {
    if(confirm("Realy delete this block?") == false)
        return;

    opener.removeBlock("block__" + block_id);
    window.closePopup();
}
function duplicateBlock(block_id) {
    opener.addBlock(0, 0, block_id);
    // window.closePopup();
}

function rewriteBiggerTextarea(field_id) {
    doCESave("pageOverlayTextArea");

    var current_value = jQuery("#pageOverlayTextArea").val();
    jQuery("#" + field_id).val(current_value);

    closePageOverlay(field_id);
}


if(typeof jQuery.browser.webkit == 'undefined' || jQuery.browser.webkit != true) {
    jQuery('body').css('position', 'relative');
}

jQuery.fn.sortDivs = function sortDivs() {
    jQuery("> div", this[0]).sort(dec_sort).appendTo(this[0]);
    function dec_sort(a, b){ return (jQuery(b).data("sort")) < (jQuery(a).data("sort")) ? 1 : -1; }
};

function submitConfigForm() {
    jQuery('#save', '#mainTaskForm').trigger('click');
}

jQuery(function() {
    var html = '';

    jQuery('.envNameField').each(function (index, ele) {
        html += '$env["' + jQuery(ele).val() + '"] ';
    });

    jQuery('#_envHelper').val(html);

    jQuery('body').trigger('InitComponents');

    jQuery('#edittask_cancel_button').on('click', function(e) {
        e.preventDefault();
        window.closePopup();
    })
});

jQuery('body').on('InitComponents', function() {
    jQuery('input.rcSwitch.doInit').removeClass('doInit').rcSwitcher({
        // Default value            // info
        theme: 'flat',          // light                    select theme between 'flat, light, dark, modern'
        width: 56,              // 56  in 'px'
        height: 20,             // 22
        blobOffset: 0,          // 2
        reverse: true,          // false                    reverse on off order
        onText: 'YES',          // 'ON'                     text displayed on ON state
        offText: 'NO',          // 'OFF'                    text displayed on OFF state
        //inputs: true,           // false                    show corresponding  inputs
        autoFontSize: true,     // false                    auto fit text size with respect to switch height
        //autoStick: true         // false                    auto stick switch to its parent side
    });
});
