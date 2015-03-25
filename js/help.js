/**
* WP On-Page SEO help library
*/

jQuery(function($) {

    $('#ops-help-bkg, .ops-help-close').click(function() {
        ops_hideHelpTooltip();
        return false;
    });

    $('.ops-help-link').click(function() {
        ops_showHelpTooltip($(this).attr('data-identifier'));
        return false;
    });

     function ops_hideHelpTooltip()
    {
        $('.ops-help-block').hide();
        $('#ops-help-bkg').hide();
    }

    function ops_showHelpTooltip(identifier)
    {
        ops_hideHelpTooltip();
        $('#ops-help-bkg').show();
        $('#ops-help-block-' + identifier).slideDown('fast');
    }
});