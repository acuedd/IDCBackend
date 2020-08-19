<?php
include_once "core/global_config.php";

class sms_report_view extends global_config implements window_view
{

    private static $_instance;
    private $strAction = "";
    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
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
        draw_header($this->lang["SMS_REPORT"]);
        theme_draw_centerbox_open($this->lang["SMS_REPORT"]);
        global_function::clearBrowserCache();
        jquery_includeLibrary("datatables");
        $this->headerScripts();
        ?>
        <div id="sms_report_app"></div>
        <?php
        $this->scripts();
        theme_draw_centerbox_close();
        draw_footer();
    }
    private function headerScripts(){
        ?>
        <link rel="stylesheet" href="/modules/sms/objects/sms_report/css/sms_report.css">
        <!--<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>-->

        <link rel="stylesheet" type="text/css" href="core/jquery/datatables/ext/buttons.dataTables.min.css" />
        <script type="text/javascript" src="core/jquery/datatables/ext/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.flash.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/jszip.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/pdfmake.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/vfs_fonts.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.html5.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.print.min.js"></script>
        <?php
    }
    private function scripts(){
        ?>
        <script>
            let url = `<?php print $this->strAction; ?>`;
            let modalHomeland = new drawWidgets();
        </script>
        <script src="/modules/sms/objects/sms_report/js/sms_report.js" defer></script>
        <?php
    }
}