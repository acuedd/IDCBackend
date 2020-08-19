<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class AddConfigSMS extends Migration
{
    private $schema;
    public function init()
    {
        $this->schema = $this->get('schema');
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "insert into wt_sms_config ( descripcion, key_validate, key_secret, fecha_creacion, active, 
                                                cod_area, max_length, url_send, short_code_id, token, username, 
                                                password, organization_id) 
                    values ( 'sms_tigo_pos', 'd6K4Rt8xh8dQbIMkyD65Oh1VENMDlVeB', 'UeAg1FLvuHgCRVAIjp0vAikhB8ee6osXE2JAjnIyCft4Y0Y8wW', 
                    '2019-09-16', 'Y', '502', '145', 'https://prod.api.tigo.com/v1/tigo/b2b/gt/comcorp/messages/organizations/5d769a7df612820001e1b4c4', 
                    'TigoPos', 'v3Zuh9k0Fa2bS35OgG1BGT0rxSgR', 'anSnDWxX1Xof9YBALSuuJb2zqKKylkr5', 'jTXAaZb0dDbboUTK', '5d769a7df612820001e1b4c4');";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $strQuery = "DELETE FROM wt_sms_config WHERE key_validate = 'd6K4Rt8xh8dQbIMkyD65Oh1VENMDlVeB'";
        Capsule::connection()->unprepared($strQuery);
    }
}
