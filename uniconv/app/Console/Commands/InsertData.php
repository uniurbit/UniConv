<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Handler;
use Illuminate\Container\Container;
use App\Models\AnagraficaUgov;
use Carbon\Carbon;
use App\Convenzione;
use App\MappingRuolo;
use App\Role;
use App\Personale;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\DatiSeeder;
use App\User;

class InsertData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uniconv:insertdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        Log::info('Esecuzione comando [ insert data ]');    
     
        $response = null;
        try{

                $adminUsers = ['francesco.calzini@uniurb.it','daniela.capponi@uniurb.it',
                    'paola.casoli@uniurb.it','alessandra.cupparoni@uniurb.it','adele.guerra@uniurb.it','joseph.fontana@uniurb.it','roberto.pandolfi@uniurb.it'];

                $persColl = Personale::where('aff_org','004960')->get();
                $this->insertUser($persColl, $adminUsers);

        } catch (\Exception $e) {
            Log::info('Errore [ InsertData ]'); 
                                        
            $handler = new Handler(Container::getInstance());
            $handler->report($e);
        }
        
    }
    
    private function onlyFirstUpper($value){
        return ucwords(strtolower($value));
    }

    public function insertUser($persColl, $adminUsers){
        foreach($persColl as $pers){
            $user = User::where('email',$pers->email)->first();
            if ($user==null){
                $user = new \App\User;                             
                $user->name = $this->onlyFirstUpper((string)$pers->nome).' '.$this->onlyFirstUpper((string)$pers->cognome);
                $user->email = $pers->email;
                $user->password = Hash::make($pers->cod_fis);   
                $user->v_ie_ru_personale_id_ab = $pers->id_ab;
                $user->save();                       

                if (in_array($pers->email, $adminUsers)){
                    $user->assignRole('admin');
                }else{
                    $user->assignRole('limited');      
                }             
            }           
        }        
    }
  
}
