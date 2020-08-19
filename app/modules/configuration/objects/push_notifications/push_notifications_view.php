<?php

include_once("core/global_config.php");

class push_notifications_view extends global_config implements window_view
{
    private static $_instance;
    private $strAction = "";

    public function __construct($arrParams)
    {
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function setStrAction($strAction)
    {
        $this->strAction = $strAction;
    }

    public function draw()
    {
        // TODO: Implement draw() method.
        draw_header($this->lang["CONFIGURATION_PUSH_NOTIFICATION"]);
        theme_draw_centerbox_open($this->lang["CONFIGURATION_PUSH_NOTIFICATION"]);
        global_function::clearBrowserCache();
        ?>
        <link rel="stylesheet" href="/modules/configuration/objects/push_notifications/css/notifications.css">
        <div id="notification_app"></div>

        <div id="notification_modal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" id="modal_title">

                        </h4>
                    </div
                    <div class="modal-body">
                        <div id="modal_body"></div>
                    </div>
                </div>

            </div>
        </div>
        <?php
        $this->scripts();
        theme_draw_centerbox_close();
        draw_footer();
    }

    private function scripts(){
        ?>
        <script>
            let url = '<?php print $this->strAction; ?>';
            let dw = new drawWidgets();
        </script>
        <script src="/modules/configuration/objects/push_notifications/js/notifications.js" defer></script>
        <?php
    }

}