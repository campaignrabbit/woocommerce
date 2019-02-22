(function ($) {
    'use strict';
    $(document).ready(function () {
        $("#submit-cmb").hide();
        $("#submit-cmb").attr('disabled', 'disabled');
    });
    $(document).on('click', '#validate_campaignrabbit_app_btn', function () {
        $("#validate_campaignrabbit_app_btn").attr('disabled', 'disabled');
        $("#campaign_loader_img").show();
        var path = $("#campaignrabbit_ajax_path").val();
        var app_id = $("#campaignrabbit_app_id");
        var secret_key = $("#campaignrabbit_secret_key");
        var message = $(".campaignrabbit_app_validation_message");
        var app_id_error = $("#app_id_error");
        var secret_key_error = $("#secret_key_error");
        app_id_error.html('');
        secret_key_error.html('');
        message.html('<p></p>');
        $("#is_campaignrabbit_app_connected").val(0);
        $.ajax({
            url: path,
            type: 'POST',
            dataType: "json",
            data: {action: 'validateCampaignRabbitAppKey', app_id: app_id.val(), secret_key: secret_key.val()},
            success: function (response) {
                $("#campaign_loader_img").hide();
                if (response.error) {
                    if (response.error.app_id) {
                        app_id_error.html(response.error.app_id);
                    }
                    if (response.error.secret_key) {
                        secret_key_error.html(response.error.secret_key);
                    }
                    if (!response.error.app_id && !response.error.secret_key) {
                        message.html('<p style="color:red;">' + response.error + '</p>');
                        $("#submit-cmb").removeAttr('disabled');
                        $("#submit-cmb").trigger("click");
                    }
                }
                if (response.success) {
                    $("#is_campaignrabbit_app_connected").val(1);
                    message.html('<p style="color:green;">' + response.success + '</p>');
                    $("#submit-cmb").removeAttr('disabled');
                    $("#submit-cmb").trigger("click");
                }
            }
        });
        $("#validate_campaignrabbit_app_btn").removeAttr('disabled');
    });
    //Disconnect app
    $(document).on('click', '#disconnect_campaignrabbit_app_btn', function () {
        $("#disconnect_campaign_loader_img").show();
        $("#campaignrabbit_app_id").val('');
        $("#campaignrabbit_secret_key").val('');
        $("#is_campaignrabbit_app_connected").val(0);
        $("#submit-cmb").removeAttr('disabled');
        $("#submit-cmb").trigger("click");
    });
})(jQuery);