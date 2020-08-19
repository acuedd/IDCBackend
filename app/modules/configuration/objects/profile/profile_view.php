<?php
include_once("core/global_config.php");
class profile_view extends global_config implements window_view {

    private static $_instance;
    private $strAction = "";
    private $arrCurrencies = array();
    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }
    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

    /**
     * @param array $arrCurrencies
     */
    public function setArrCurrencies( $arrCurrencies)
    {
        $this->arrCurrencies = $arrCurrencies;
    }
    public function draw()
    {
        draw_header($this->lang["CONFIGURATION_COLORS"]);
        theme_draw_centerbox_open($this->lang["CONFIGURATION_COLORS"]);
        global_function::clearBrowserCache();
        $this->headerScripts();
        ?>
        <div id="profile_div"></div>
        <?php
        $this->scripts();
        theme_draw_centerbox_close();
        draw_footer();
    }
    private function headerScripts(){
        ?>
        <link rel="stylesheet" href="modules/configuration/objects/profile/css/style.css">
        <?php
    }
    private function scripts(){
        ?>
        <script src="modules/configuration/objects/profile/js/profile.js"></script>
        <script>
            let strAction = '<?php print $this->strAction; ?>';
            const lang = {
                CONFIGURATION_ADD: '<?php print $this->lang["CONFIGURATION_ADD"]; ?>',
                CONFIGURATION_TITLE_TABLE: '<?php print $this->lang["CONFIGURATION_TITLE_TABLE"]; ?>',
                CONFIGURATION_TITLE_MODAL: '<?php print $this->lang["CONFIGURATION_TITLE_MODAL"]; ?>',
                CONFIGURATION_CURRENCY_NAME: '<?php print $this->lang["CONFIGURATION_CURRENCY_NAME"]; ?>',
                CONFIGURATION_CURRENCY_CODE: '<?php print $this->lang["CONFIGURATION_CURRENCY_CODE"]; ?>',
                CONFIGURATION_CURRENCY_RATE: '<?php print $this->lang["CONFIGURATION_CURRENCY_RATE"]; ?>',
                CONFIGURATION_CURRENCY_REVERSE_RATE: '<?php print $this->lang["CONFIGURATION_CURRENCY_REVERSE_RATE"]; ?>',
                DIMENSION_WITDT: '<?php print $this->lang["DIMENSION_WITDT"]; ?>',
                DIMENSION_HEIGTH: '<?php print $this->lang["DIMENSION_HEIGTH"]; ?>',
                RECOMENDATION_LINK: 'Si no es un link de genius no olvides colocar https o http, ejemplo: "<b>https:</b>//www.google.com/"'
            };
            let dw = new drawWidgets();

            let apiProfile = {
                baseRequest(type, options = {}){
                    return fetch(`${strAction}&op=${type}`, options);
                },
                Types: {
                    getByType(str){
                        let real = new FormData();
                        real.append('type', str);
                        return apiProfile.baseRequest(`getByType`, {
                            method: 'POST',
                            body: real
                        });
                    }
                },
                Menu: {
                    getMenuItems(){
                        return apiProfile.baseRequest(`getMenuItems`);
                    }
                },
                Caption: {
                    getAllCaptions(){
                        return apiProfile.baseRequest(`getAllCaptions`);
                    }
                },
                Email:{
                    GetImagesCorreo(){
                        return apiProfile.baseRequest(`getImageCorreo`)
                    }
                }
            };

            let config = new profileJs({
                body: "profile_div",
                widget: dw,
                action: strAction,
            });

            config.init();
        </script>
        <?php
    }
}