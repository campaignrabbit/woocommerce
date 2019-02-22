<?php
if (!defined('ABSPATH')) exit;

class CMB2_Field_Campaignrabbit_Logs
{

    /**
     * Current version number
     */
    const VERSION = '1.0.0';

    /**
     * Initialize the plugin by hooking into CMB2
     */
    public function __construct()
    {
        add_filter('cmb2_render_campaignrabbit_logs', array($this, 'render_campaignrabbit_logs'), 10, 5);
    }

    /**
     * Render select box field
     */
    public function render_campaignrabbit_logs($field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object)
    {
        ?>
        <script>
            function clearLog() {
                confirm("<?php echo __('Are you sure want to clear log?', CRIFW_TEXT_DOMAIN);  ?>")
                {
                    (function ($) {
                        $.ajax({
                            url: "<?php echo admin_url('admin-ajax.php') ?>",
                            type: 'POST',
                            dataType: "json",
                            data: {action: 'clearCampaignrabbitLogs'},
                            success: function (response) {
                                if (response.error) {
                                    alert(response.error);
                                }
                                if (response.success) {
                                    alert(response.success);
                                    window.location.reload();
                                }
                            }
                        });
                    })(jQuery);
                }
            }
        </script>
        <span style="float: right;">
            <button type="button" onclick="clearLog();"
                    class="button button-primary"><?php echo __('Clear Logs', CRIFW_TEXT_DOMAIN); ?></button></span>
        <?php
        if (file_exists(CRIFW_LOG_FILE_PATH)) {
            if ($fh = fopen(CRIFW_LOG_FILE_PATH, 'r')) {
                while (!feof($fh)) {
                    $line = fgets($fh);
                    echo nl2br($line);
                }
                fclose($fh);
            }
        } else {
            echo __('There is no log to display!', CRIFW_TEXT_DOMAIN);
        }
    }
}

new CMB2_Field_Campaignrabbit_Logs();