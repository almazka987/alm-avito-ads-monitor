jQuery( document ).ready( function ( $ ) {
    $('.last-monitor-table .js-avito-exclude').on('click', function(event) {
        event.preventDefault();
        var itemID = $(this).data('exclude-id');
        $hideContainer = $(this).parent('.monitor-item').parent('.item' + itemID);
        $.ajax({
            type: "POST",
            data: {
                action: 'exclude_avito_item',
                itemID: itemID,
            },
            url: ajaxUrl.url,
            beforeSend: function(){
                $('.last-monitor-loader').fadeIn();
            },
            success: function(data) {
                if(+data === 1) {
                    $hideContainer.fadeOut();
                    $('.last-monitor-loader').fadeOut();
                }
            }
        });
    });

    $('.last-monitor-option-table .js-bulk-avito-exclude').on('click', function(event) {
        event.preventDefault();
        var excludeIDs = [];
        $('input[name="bulk-exclude-checks"]').each(function(idx, el) {
            if ($(el).is(':checked')) {
                excludeIDs.push($(el).data('exclude-id'));
            }
        });
        $.ajax({
            type: "POST",
            data: {
                action: 'exclude_avito_item',
                excludeIDs: excludeIDs,
            },
            url: ajaxUrl.url,
            beforeSend: function(){
                $('.last-monitor-loader').fadeIn();
            },
            success: function(data) {
                if(data.length > 0) {
                    var hideIds = JSON.parse(data);
                    if(Array.isArray(hideIds)) {
                        hideIds.forEach(el => {
                            $('.item' + el).fadeOut();
                        });
                    }
                    $('.last-monitor-loader').fadeOut();
                }
            }
        });
    });
});