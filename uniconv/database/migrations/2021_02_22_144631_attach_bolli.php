<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AttachmentType;
use App\Convenzione;

class AttachBolli extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attachType = AttachmentType::where('codice','ALLEGATO_BOLLI')->first();
        if ($attachType==null){
            $attachType = new AttachmentType();
            $attachType->codice = 'ALLEGATO_BOLLI';
            $attachType->descrizione = 'Modulo attestazione pagamento imposta di bollo';
            $attachType->descrizione_compl = 'Modulo attestazione pagamento imposta di bollo';
            $attachType->parent_type = Convenzione::class;   
            $attachType->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        AttachmentType::where('codice','ALLEGATO_BOLLI')->delete();
    }
}
