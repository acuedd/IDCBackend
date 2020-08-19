<?php
/**
 * Created by PhpStorm.
 * User: NelsonRodriguez
 * Date: 23/04/2019
 * Time: 11:14 AM
 */

class administration_model extends global_config
{
    private static $_instance;

    public function __construct($arrParams)
    {
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams)
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function getNotificationsExist($strFilter = "", $intIDUser = 0)
    {
        $strFilterQuery = "";
        $strInnerJoin = "";
        $strSelect = "*";
        if(!empty($strFilter)){
            $strFilterQuery = $strFilter;
        }
        if(!empty($intIDUser)){
            $strSelect = " NA.id, NA.sw_user_type, NA.title, NA.key_char_to_draw, NA.message, NA.url_window, NA.class_style_notification,
                            NA.bool_filter_date, NA.date_to, NA.date_from, NUNS.id_notification, NUNS.id_user ";

            $strInnerJoin = " LEFT JOIN wt_notification_users_no_show AS NUNS
                         ON NA.id = NUNS.id_notification ";
        }

        $strQuery = "SELECT {$strSelect} FROM wt_notification_admin AS NA
                        {$strInnerJoin} WHERE 1 {$strFilterQuery}";
        $this->appendDebug($strQuery);
        /*debug::drawdebug($strQuery);*/

        $qTMP = db_query($strQuery);
        $arrReturn = [];
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $dateNow = date('Y-m-d');
                /*debug::drawdebug($intIDUser, "intIDUSER");
                debug::drawdebug($rTMP, "rTMP");*/
                if($rTMP["bool_filter_date"] == "Y"){
                    if ( strtotime($rTMP['date_to']) > strtotime($dateNow) || strtotime($rTMP['date_from']) < strtotime($dateNow)){
                        continue;
                    }
                }

                if(!empty($intIDUser)){
                    if($rTMP["id_user"] != $intIDUser){
                        /*debug::drawdebug("asdf asdf asdf");*/
                        $arrReturn[] = $rTMP;
                    }
                }
                else{
                    /*debug::drawdebug("no debería de entrar");*/
                    $arrReturn[] = $rTMP;
                }
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        /*debug::drawdebug($arrReturn);
        die;*/
        return $arrReturn;
    }

    public function deleteNotificationByID($intIDNotification)
    {
        $strQuery = "DELETE FROM wt_notification_admin WHERE id = '$intIDNotification'";
        $this->appendDebug($strQuery);
        return db_query($strQuery);
    }

}