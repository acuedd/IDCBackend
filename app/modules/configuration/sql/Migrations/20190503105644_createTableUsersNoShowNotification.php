<?php

use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

class CreateTableUsersNoShowNotification extends Migration
{
    protected $table;
    protected $schema;

    public function init()
    {
        // TODO: Change the autogenerated stub
        $this->table = "wt_notification_users_no_show";
        $this->schema = $this->get('schema');
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $this->schema->create($this->table, function (Blueprint $table) {
            $table->increments("id");
            $table->integer("id_notification");
            $table->integer("id_user");
            $table->timestamps();
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->schema->drop($this->table);
    }
}
