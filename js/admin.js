jQuery(function($) {
    var ops_terminated = false;

    $('#ops_factor-all').change(function() {
        $('#ops_admin-input-factors input:checkbox:enabled')
            .attr('checked', $(this).attr('checked')? 'checked' : null);
    });

    $('.ops_post-all').change(function() {
        $('#ops_admin-table-posts input:checkbox:enabled')
            .attr('checked', $(this).attr('checked')? 'checked' : null);
    });

    $('.ops_post-type').change(function() {
        $('#ops_admin').submit();
    });

    $('.ops_post_status-link').click(function() {
        $('#post_status').val($(this).attr('data-post_status'));
        $('#ops_admin').submit();
        return false;
    });

    $('.ops_paged-link').click(function() {
        $('#paged-hidden')
            .attr('name', 'paged')
            .val($(this).attr('data-paged'));
        $('#paged-text').attr('name', '_paged');
        $('#ops_admin').submit();
        return false;
    });

    $('.ops_order-link').click(function() {
        $('#orderby').val($(this).attr('data-orderby'));
        $('#order').val($(this).attr('data-order'));
        $('#ops_admin').submit();
        return false;
    });

    $('.ops_keyword').focus(function() {
        $('.ops_selected[value="' + $(this).attr('data-post_id') + '"]')
            .attr('checked', 'checked');
    });

    $(".ops_navigation-text").keypress(function(event) {
        if (event.which == 13) {
            $('#ops_admin').submit();
            return false;
        }
    });

        // mass optimize
    $('.ops_optimization-submit').click(function() {
        var keywords = [];
        var elems = $('#ops_admin-table-posts').find('input:checkbox:checked:not(.ops_post-all)');
        var itemCount =  elems.length;

        if (0 != itemCount) {
            for (var i = 0; i < itemCount; i++) {
                var postId  = $(elems[i]).val();
                var keyword = $(elems[i]).parents('tr.ops_admin-table-row').find('input.ops_keyword').val();
                keywords[i] = [postId, keyword];
            };

            ops_showProgress(null);
            ops_optimize(keywords, ops_getFactors(), ops_getExtraContentMode(),
                itemCount, 0, true);
        } else {
            ops_message('Please select some posts to process', 'error');
        }
    });

    // single optimize
    $('.tools-column button.ops_optimizing_item-button').click(function(){
        var keywords = [];
        var postId  = $(this).parents('tr.ops_admin-table-row').find('input:checkbox').val();
        var keyword = $(this).parents('tr.ops_admin-table-row').find('input.ops_keyword').val();
        keywords[0] = [postId, keyword];

        ops_showProgress($('#ops_optimizing-' + postId));
        ops_optimize(keywords, ops_getFactors(), ops_getExtraContentMode(),
            0, 0, true);
    });

    $('.ops_optimizing-cancel').click(function() {
        ops_terminate();
    });

    // single keyword
    $('button.ops_set_keyword_item-button').click(function(){
        var title = $(this).attr('data-title');
        var postId = $(this).attr('data-id');
        var elem = $('#ops_admin-table-row-'+postId);
        ops_setKeyword(elem, title);
    });

    // mass set keywords
    $('.ops_set_all_keywords-button').click(function(){
        $('#ops_admin-table-posts tr.ops_admin-table-row').each(function(n, elem){
           if ('' == $(elem).find('input.ops_keyword').val()) {
               var title = $(elem).find('input.ops_title:hidden').val();
               if ('' != title) {
                    ops_setKeyword(elem, title);
               }
           }
        })
    });

    function ops_message(message, type)
	{
        var element = jQuery('<strong />').text(message);
        element = jQuery('<p />').append(element);
        element = jQuery('<div class="' + type + '"  />').append(element);
        jQuery('#messages-container')
            .empty()
            .append(element);
    }

	function ops_showProgress(elem)
	{
		$('div.updated, div.error').remove();
		if (elem) {
			$('button', $(elem)).hide();
            $('span', $(elem)).show();
            $('img', $(elem)).css({ 'visibility': 'visible'});
		} else {
			$('.ops_optimization-submit').attr('disabled', 'disabled');
            $('#ops_optimizing-progress img.ops_ajax-optimizing').css('visibility', 'visible').show();
            $("#ops_optimizing-progressbar").progressbar({ value: 0 });
            $("#ops_optimizing-progressbar").progressbar( 'option', 'value', 0 );
            $('#ops_optimizing-progress').slideDown();

            //Auto scroll:
            if (!ops_isScrolledIntoView($('#ops_optimizing-progress'))) {
                $('html, body').animate({
                    scrollTop: $("#ops_optimizing-progress").position().top
                }, 1000);
            }
		}
	}

	function  ops_hideProgress(elem)
	{
		if (elem && -1 != elem) {
            $('span', $(elem)).hide();
            $('img', $(elem)).css({ 'visibility': 'hidden'});
            $('button', $(elem)).show();
        } else {
            $('#ops_optimizing-progress').slideUp();
            $('.ops_optimization-submit').removeAttr('disabled');
		}
	}

    function ops_changePostStatusIcon(result)
    {
        if (result['postId'] > -1) {
            var elem = $('tr#ops_admin-table-row-' + result['postId']);
            var className = 'ops_status_optimize-yes';
            if (result['status']) {
                elem.find('.ops_status').addClass(className);
            } else {
                elem.find('.ops_status').removeClass(className);
                elem.find('.ops_selected').attr('checked', false);
            }
        }
    }

    function ops_optimize(keywords, factors, extraContentMode, itemCount,
        currentItem, saveFactors)
    {
        var single = (0 == itemCount);
        if (keywords.length > 0) {
            var keyword = keywords.shift();
			var postId = keyword[0];
            if (!single) {
                currentItem++;
                $('#ops_optimizing-current').html(currentItem);
                $('#ops_optimizing-all').html(itemCount);
            }
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action:                 'ops',
                    controller:             'admin',
                    controller_action:      'ajax-optimize',
                    postId:                 postId,
                    keyword:                keyword[1],
                    factors:                factors,
                    extra_content_mode:     extraContentMode,
                    save_factors:           saveFactors
                },
                dataType: 'json',
                context: single ? $('#ops_optimizing-' + postId) : -1,
                success: function(data) {
                    if ('' != data['error']) {
						ops_message(data['error'], 'error');
						ops_hideProgress(this);
					} else {
                        if (itemCount > 0) {
						    $("#ops_optimizing-progressbar").progressbar('option', 'value', Math.round(100*currentItem/itemCount));
                        }
                        ops_changePostStatusIcon(data['result']);
                        ops_optimize(keywords, factors, extraContentMode,
                            itemCount, currentItem, false);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (!ops_terminated) {
                        ops_message('AJAX connection error', 'error');
                        ops_hideProgress(this);
                    }
                },
                complete: function() {
                    if (-1 != this) {
                        ops_hideProgress(this);
                    }
				}
            });
        } else {
            if (itemCount) {
                ops_hideProgress(null);
                ops_message('Mass Optimize: processed ' + itemCount + ' posts', 'updated');
            }
        }
    }

    function ops_terminate()
    {
        ops_terminated = true;
        $('#ops_admin').submit();
    }

	function ops_getFactors()
	{
		var factors = [];
        $('li.ops_admin-factor input:checkbox:checked').each(function(n, elem){
            factors[n] = $(elem).val();
        });
		return factors;
	}

    function ops_getExtraContentMode()
    {
        return $('input.ops-extra_content_mode-option:checked').val();
    }

    function ops_isScrolledIntoView(elem)
    {
        var docViewTop = $(window).scrollTop();
        var docViewBottom = docViewTop + $(window).height();

        var elemTop = $(elem).offset().top;
        var elemBottom = elemTop + $(elem).height();

        return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom)
          && (elemBottom <= docViewBottom) &&  (elemTop >= docViewTop) );
    }

    function ops_setKeyword(elem, title){
        $(elem).find('input.ops_keyword').val(title);
        $(elem).find('input.ops_selected').attr('checked', 'checked');
    }

    //
    // Options screen
    //

    $('#auto_optimization_keyword').focus(function() {
        $('#auto_optimization-option-keyword').attr('checked', 'checked');
    });

    $('#default_factors-all').change(function() {
        $('#default_factors input:checkbox:enabled')
            .attr('checked', $(this).attr('checked')? 'checked' : null);
    });

    $('#bing_api_key').change(function() {
        if ('' == $.trim($(this).val())) {
            $('#default_factors-option-RelatedTerms')
                .attr('disabled', 'disabled')
                .removeAttr('checked');
        } else {
            $('#default_factors-option-RelatedTerms')
                .removeAttr('disabled');
        }
    });
});