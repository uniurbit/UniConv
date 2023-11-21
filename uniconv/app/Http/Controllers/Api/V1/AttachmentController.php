<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Attachment;
use App\Http\Controllers\Controller;
use Validator;
use App\Convenzione;
use Storage;
use App\Http\Controllers\SoapControllerTitulus;
use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Service\TitulusHelper;
use Auth;
use Illuminate\Support\Str;
class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Attachment::all();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Attachment::find($id);
    }

  
    public function query(Request $request){       

        $queryBuilder = new QueryBuilder(new Dipartimeno, $request);
                
        return $queryBuilder->build()->paginate();       

    }

    public function uploadFile(Request $request){
        
        if (!Auth::user()->hasPermissionTo('create attachments')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $rules = array();

        $validator = Validator::make($request->all(), $rules);
        if ($validator-> fails()){
            return $this->respondValidationError('Validazione fallita.', $validator->errors());
        }

        
        if ($request->model_type == 'App\\Convenzione'){
            //cancellazione
            Convenzione::findOrFail($request->model_id);
        }
        
        $attachment = $this->saveAttachment($request->all());       
        if ($attachment){
            //file caricato con successo
            //ritornare id del file 
            return Attachment::with('attachmenttype')->find($attachment->id);                             
        }                
        return response()->json('Il documento '.$request->get('filename').' non è stato memorizzato', 404);
    }

    public function saveAttachment($data){              
        $attachment = new Attachment($data);              
        if (array_key_exists('filevalue',$data) && $attachment->loadStream($data['filevalue']) != null ){                
            $attachment->save();
        }else{                            
            if ($attachment->nrecord && $attachment->num_prot && $attachment->createLink($attachment->num_prot)){
                $attachment->save();
            } else{
                if ($attachment->createEmptyFile()){
                    $attachment->save();
                }else{
                    throw new Exception("Error file ".$valore['filename']." not saved", 1);                
                }                
            }
        }   
        return $attachment;          
    }


    public function deletefile($id){
        if (!Auth::user()->hasPermissionTo('delete attachments')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        Attachment::find($id)->delete();
        return response()->json(null, 204);
    }

    public function download($id){

        if (!Auth::user()->hasPermissionTo('view attachments')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }        
        
        $attach = Attachment::find($id);
        if ($attach->num_prot){
            $app = TitulusHelper::downloadAttachment($attach->num_prot,$attach->filename);
            if ($app){
                $attach = $this->getAttachmentContent($attach, $app);
            }
        }else{
            if ($attach['type'] != 'empty' && $attach['filepath']){                        
                $attach['filevalue'] = base64_encode(Storage::get($attach->filepath));
            }
        }        
        return $attach;        
    }

    public function getAttachmentContent($attach, $app)
    {
        //se application/xml oppure application/octet-stream e file id termina con .xml
        if ($attach->attachmenttype_codice=="FATTURA_ELETTRONICA" && 
                ($app->mimeType == "application/xml" || 
                    ($app->mimeType == "application/octet-stream" && Str::endsWith(strtolower($app->id),'.xml')))
            ){
        
                $pdf = TitulusHelper::createFatturaPA($app->content);
                $attach['filevalue'] = base64_encode($pdf->output());
                if ($attach->filetype == 'link'){
                    $attach['filename'] = $app->title.'.pdf';
                }
        }else{
            $attach['filevalue'] =  base64_encode($app->content);                                                    
            if ($attach->filetype == 'link'){                     
                if ($this->mime2ext($app->mimeType)){
                    $attach['filename'] = $app->title.'.'.$this->mime2ext($app->mimeType);
                }else{
                    $attach['filename'] = $app->title;
                }                            
            }                                            
        }

        return $attach;
    }

    public function getTitulusDocumentURL($id){

        if (!Auth::user()->hasPermissionTo('view attachments')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }   

        $attach = Attachment::find($id);        
        if ($attach->num_prot){
            $sc = new SoapControllerTitulus(new SoapWrapper);

            $resp = $sc->getDocumentURL($attach->num_prot);
            $parse = parse_url($resp);        
            if (isset($parse['query'])){
                return [
                    'url'=> config('titulus.url').$parse['path'].'?'.$parse['query']
                ];
            }else{
                return [
                    'url'=>$resp 
                ];
            }
        }        

        return response()->json(null);
    }


    private function mime2ext($mime) {
        $mime_map = [                          
            'image/bmp'                                                                 => 'bmp',
            'image/x-bmp'                                                               => 'bmp',
            'image/x-bitmap'                                                            => 'bmp',
            'image/x-xbitmap'                                                           => 'bmp',
            'image/x-win-bitmap'                                                        => 'bmp',
            'image/x-windows-bmp'                                                       => 'bmp',
            'image/ms-bmp'                                                              => 'bmp',
            'image/x-ms-bmp'                                                            => 'bmp',
            'application/bmp'                                                           => 'bmp',
            'application/x-bmp'                                                         => 'bmp',
            'application/x-win-bitmap'                                                  => 'bmp',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'image/gif'                                                                 => 'gif',
            'image/jpeg'                                                                => 'jpeg',
            'image/pjpeg'                                                               => 'jpeg',         
            'application/pkcs7-mime'                                                    => 'p7m',
            'application/x-pkcs7-mime'                                                  => 'p7c',
            'application/x-pkcs7-certreqresp'                                           => 'p7r',
            'application/pkcs7-signature'                                               => 'p7s',
            'application/pdf'                                                           => 'pdf',
            'application/octet-stream'                                                  => 'pdf',
            'image/png'                                                                 => 'png',
            'image/x-png'                                                               => 'png',
            'application/vnd.ms-office'                                                 => 'doc',
            'application/msword'                                                        => 'doc',                        
            'application/msexcel'                                                       => 'xls',
            'application/x-msexcel'                                                     => 'xls',
            'application/x-ms-excel'                                                    => 'xls',
            'application/x-excel'                                                       => 'xls',
            'application/x-dos_ms_excel'                                                => 'xls',
            'application/xls'                                                           => 'xls',
            'application/x-xls'                                                         => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.ms-excel'                                                  => 'xlsx',                                    
            'text/plain'                                                                => 'txt',
        ];
    
        return isset($mime_map[$mime]) ? $mime_map[$mime] : false;
    }

}
