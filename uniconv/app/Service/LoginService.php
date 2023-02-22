<?php

namespace App\Service;

use App\Personale;
use App\MappingRuolo;
use Illuminate\Support\Facades\Log;

class LoginService implements ApplicationService
{

    public function isAuthorized($email){
        $pers = Personale::FindByEmail($email)->first(); 
        return $pers->isDocente() || $pers->isPta();
    }

   /**
    * @param $email
    * i possibili ruoli sono 
    * ADMIN per gli afferenti al ssia
    * @return 
    */
   
    public function findUserRoleAndData($email)
    {   
        Log::info('findUserRole [ '. $email .']');              
        $pers = Personale::FindByEmail($email)->first();
            
        return LoginService::roleAndData($pers);        
    }
    
    public function findUserRoleAndDataById($id)
    {   
        Log::info('findUserRole [ '. $id .']');              
        $pers = Personale::FindByIdAB($value['v_ie_ru_personale_id_ab'])->first();           

        return LoginService::roleAndData($pers);     
    }

    public static function roleAndData($pers){
        $data = [
            'id_ab' => $pers->id_ab,
        ];

        Log::info('Personale [ '. $pers->nome .']');         
        if ($pers->ruolo->isDocente()){
            $data['ruoli'] = ['op_docente'];
            return $data;
        }

        if ($pers->ruolo->isPta()){            
            $data['ruoli'] = MappingRuolo::where('unitaorganizzativa_uo',$pers->unita->uo)->get()->map(function ($mapping) {
                return $mapping->role->name;
            })->toArray();

            if (count($data['ruoli'])>0){                
                return $data;                    
            }else{
                Log::info('MappingRuolo [ NON TROVATO ]');   
            }
            
            $data['ruoli'] = ['viewer'];    
            return $data;
        }

        Log::info('findUserRole [ NON TROVATO ]');   
        return null;
    }


}