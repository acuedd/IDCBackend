<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class UpdateTableWtProfileConfiguration extends Migration
{

    public function up()
    {
        $table = 'wt_profile_configuration';
        $strQuery = "UPDATE {$table}
                     SET description = 'Esta imagen puede ser su logotipo, imagotipo. Las imagenes se adaptaran pero se recomienda un tamaño de 200x50 para el menu maximizado y en formato png.'
                     WHERE specified = 'img-menu-exp';
                     UPDATE {$table}
                     SET description = 'Esta imagen puede ser su isotipo. Se recomiendan imagenes de 200x200 para el menu minimizado y en formato png.'
                     WHERE specified = 'img-menu-min';
                     UPDATE {$table}
                     SET description = 'Esta imagen puede ser el isologo, logotipo de su empresa. Se recomiendan imagnes en formato png, esta se ajustara al tamaño de la pantalla y se mostrara centrada.'
                     WHERE specified = 'img-start';
                     UPDATE {$table}
                     SET description = 'En esta imagen puede colocar un fondo en la pantalla de inicio, puede ser una textura de preferencia para que la vista no se vea saturada con el logotipo de la empresa y una imagen con muchos elementos. Combinable con color de fondo.'
                     WHERE specified = 'img-start-bkg';
                     UPDATE {$table}
                     SET description = 'Este color es combinable con imagen de fondo, la imagen se repetira si hay color de fondo'
                     WHERE specified = 'color-start-bkg';
                     UPDATE {$table}
                     SET description = 'Este color cambiara el menu, es combinable con el color de degradado'
                     WHERE specified = 'color-menu';
                     UPDATE {$table}
                     SET description = 'Este color es combinable con el color de menu, es opcional'
                     WHERE specified = 'color-deg';";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
