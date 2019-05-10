(function ($) {
    'use strict';
    $(document).on('click', '#sync_orders_campaignrabbit_app_btn', function () {
        var sync_btn = $("#sync_orders_campaignrabbit_app_btn");
        var limit = $("#sync_batch_count").val();
        var count = $("#total_orders_found").val();
        sync_btn.attr('disabled', 'disabled');
        var msg_container = $("#ajax_message_container");
        msg_container.html('');
        msg_container.hide();
        if (parseInt(count) > 0) {
            doOrderSync(count, limit, 0);
        } else {
            var msg = '<p>' + msg_container.data('noorder') + '</p>';
            msg_container.append(msg);
            msg_container.show();
        }
    });
    $(document).on('click', '.remove-from-queue-table', function () {
        var remove = $(this).data('remove');
        var hook = $(this).data('hook');
        var path = $("#campaignrabbit_ajax_path").val();
        $(this).attr('disabled', true);
        var msg_container = $("#ajax_message_container");
        $.ajax({
            url: path,
            type: 'POST',
            dataType: "json",
            data: {action: 'removeFromQueue', hook: hook, remove: remove},
            success: function (response) {
                window.location.reload();
            }
        });
    });

    function doOrderSync(count, limit, start) {
        var path = $("#campaignrabbit_ajax_path").val();
        var msg_container = $("#ajax_message_container");
        $.ajax({
            url: path,
            type: 'POST',
            dataType: "json",
            data: {action: 'syncOldOrdersWithCampaignrabbit', count: count, limit: limit, start: start},
            success: function (response) {
                if (response.success) {
                    $("#sync_orders_campaignrabbit_app_btn").removeAttr('disabled');
                    var msg = '<p>' + msg_container.data('complete') + '</p>';
                    msg_container.append(msg);
                    msg_container.show();
                }
                if (response.dopatch) {
                    if (parseInt(response.total) > 0) {
                        var msg = '<p>' + response.total + msg_container.data('message') + '</p>';
                        msg_container.append(msg);
                        msg_container.show();
                    }
                    doOrderSync(response.total, limit, response.start);
                }
            }
        });
    }
})(jQuery);