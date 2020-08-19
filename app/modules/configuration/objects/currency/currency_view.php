<?php
include_once("core/global_config.php");
class currency_view extends global_config implements window_view {

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
        draw_header($this->lang["CONFIGURATION_CURRENCY"]);
        theme_draw_centerbox_open($this->lang["CONFIGURATION_CURRENCY"]);
        global_function::clearBrowserCache();
        $this->headerScripts();
        ?>
        <div id="currency_div"></div>
        <?php
        $this->scripts();
        theme_draw_centerbox_close();
        draw_footer();
    }

    private function headerScripts(){
        ?>
        <script src="core/packages/selectPure/selectPure.min.js"></script>
        <link rel="stylesheet" href="core/packages/selectPure/selectPure.css">
        <link rel="stylesheet" href="modules/configuration/objects/currency/css/currency.css">
        <?php
    }


    private function scripts()
    {
        utf8_encode_array($this->arrCurrencies);
        ?>
        <script src="modules/configuration/objects/currency/js/coin_config.js"></script>
        <script src="modules/configuration/objects/currency/js/coins.js"></script>
        <script>
            let objCurrenciesDB = {};
            <?php
            if($this->arrCurrencies){
                ?>
                objCurrenciesDB = <?php print json_encode($this->arrCurrencies); ?>
                <?php
            }
            ?>

            let strAction = '<?php print $this->strAction; ?>';
            let myCoinsDB = {};
            let masterCoin = "";
            for(let coin in objCurrenciesDB){
                myCoinsDB[objCurrenciesDB[coin].area_code] = objCurrenciesDB[coin].area_code;
                if(objCurrenciesDB[coin].pivot == "Y"){
                    masterCoin = objCurrenciesDB[coin].area_code;
                }
            }

            const lang = {
                CONFIGURATION_ADD: '<?php print $this->lang["CONFIGURATION_ADD"]; ?>',
                CONFIGURATION_TITLE_TABLE: '<?php print $this->lang["CONFIGURATION_TITLE_TABLE"]; ?>',
                CONFIGURATION_TITLE_MODAL: '<?php print $this->lang["CONFIGURATION_TITLE_MODAL"]; ?>',
                CONFIGURATION_CURRENCY_NAME: '<?php print $this->lang["CONFIGURATION_CURRENCY_NAME"]; ?>',
                CONFIGURATION_CURRENCY_CODE: '<?php print $this->lang["CONFIGURATION_CURRENCY_CODE"]; ?>',
                CONFIGURATION_CURRENCY_RATE: '<?php print $this->lang["CONFIGURATION_CURRENCY_RATE"]; ?>',
                CONFIGURATION_CURRENCY_REVERSE_RATE: '<?php print $this->lang["CONFIGURATION_CURRENCY_REVERSE_RATE"]; ?>',
            };
            let dw = new drawWidgets();

            let config = new configCurrencies({
                body: "currency_div",
                coins: coins,
                coinsDB: myCoinsDB,
                widget: dw,
                action: strAction,
                masterCoin: masterCoin,
            });

            config.init();
        </script>
        <?php
    }
}