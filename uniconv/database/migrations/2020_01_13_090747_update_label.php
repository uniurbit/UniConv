<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AttachmentType;
use App\MappingUfficio;

class UpdateLabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $attachType = AttachmentType::where('codice','DDD')->first();
        if ($attachType){
            $attachType->descrizione = 'Decreto del direttore di dipartimento';
            $attachType->descrizione_compl = 'Decreto del direttore di dipartimento';
            $attachType->save();
        }

        $mapping = MappingUfficio::where('strutturainterna_cod_uff','SI000087')->first();
        $mapping->descrizione_uo = 'Dipartimento di Scienze della Comunicazione, Studi Umanistici e Internazionali (DISCUI)';
        $mapping->unitaorganizzativa_uo = '005579';
        $mapping->save();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $attachType = AttachmentType::where('codice','DDD')->first();
        $attachType->descrizione = 'Disposizione del direttore di dipartimento';
        $attachType->descrizione_compl = 'Disposizione del direttore di dipartimento';
        $attachType->save();

        $mapping = MappingUfficio::where('strutturainterna_cod_uff','SI000087')->first();
        $mapping->descrizione_uo = 'Dipartimento di Scienze della Comunicazione, Studi Umanistici e Internazionali: Storia, Culture, Lingue, Letterature, Arti, Media (DISCUI)';
        $mapping->unitaorganizzativa_uo = '004940';
        $mapping->save();

    }
}
