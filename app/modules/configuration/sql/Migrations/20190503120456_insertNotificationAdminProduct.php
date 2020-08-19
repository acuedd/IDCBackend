<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class InsertNotificationAdminProduct extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO wt_notification_admin (sw_user_type, title, key_char_to_draw, message, url_window, class_style_notification) 
                        VALUES ('vendedor', '', 'drag and drop', 'Ordernar columnas arrastrando los títulos manteniendo click sobre los mismos', 'sale/config/product', 'notificationDefault');";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Capsule::connection()->unprepared("TRUNCATE wt_notification_admin;");
    }
}
