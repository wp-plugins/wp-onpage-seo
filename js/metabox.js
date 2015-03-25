jQuery(function($) {
    $.fn.setDisabled = function(value) {
        if (typeof this['prop'] === 'function') {
            this.prop('disabled', value);
        } else if (value) {
            this.attr('disabled', 'disabled');
        } else {
            this.removeAttr('disabled');
        }

        if (value) {
            this.addClass('disabled');
        } else {
            this.removeClass('disabled');
        }

        return this;
    };

    $('.ops_metabox-button_update').click(function() {
        this.disabled = true;
        $(this).addClass('button-primary-disabled');
        $('#ops_metabox-update_ajax_loader').css('visibility', 'visible');
        blockSave = true;
        window.onbeforeunload = null;
        $('#post').submit();
    });

   	$('#ops_metabox-factor-all').change(function() {
        $('#ops_metabox-input-factors input:checkbox:enabled')
            .attr('checked', $(this).attr('checked')? 'checked' : null);
    });

    $('#ops_keyword').keyup(function() {
        $('#ops_metabox-input-factors li.ops_metabox-factor').attr('class', 'ops_metabox-factor_none');
        $('#ops_metabox-input-factors .ops_metabox-factor-force').remove();
        $('#ops_metabox-input-factors input[type="checkbox"]').setDisabled(false);
    })

    function ops_show_message(message, type)
    {
        ops_remove_message();
        $('#ops_metabox .ops_metabox-message').append('<li class="' + type + '">' + message + '</li>');
    }

    function ops_remove_message()
    {
        $('#ops_metabox .ocs_metabox-message').empty();
    }

    function ops_htmlEscape(string) {
        return string.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
});