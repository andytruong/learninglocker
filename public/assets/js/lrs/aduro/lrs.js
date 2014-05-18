(function($) {

$(document).ready(function() {
    toggleTextBoxs($('input[name=auth_service]:checked').val());
});

$('input[name=auth_service]').change(function(e) {
    toggleTextBoxs($(this).val());
});

function toggleTextBoxs(val) {
    var disableTextBoxs = ['#token', '#auth_cache_time', '#auth_service_url'];

    if (val == 1) {
        for (var i = 0; i < disableTextBoxs.length; i++) {
            $(disableTextBoxs[i]).removeAttr('disabled');
        }
    } else {
        for (var i = 0; i < disableTextBoxs.length; i++) {
            $(disableTextBoxs[i]).attr('disabled', 'disabled');
        }
    }
}

})(jQuery);