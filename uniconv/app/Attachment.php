<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Crypt;
use File as FileHelper;
use Storage;
use Symfony\Component\HttpFoundation\File\File as FileObj;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Emadadly\LaravelUuid\Uuids;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Convenzione;
use App\Observers\UserActionsObserver;
use Illuminate\Support\Arr;

class Attachment extends Model

{
    use Uuids;

    /**
     * @property int    id
     * @property string uuid
     * @property int    model_id
     * @property string model_type
     * @property string disk
     * @property string filepath  the full path on storage disk
     * @property string filename
     * @property string filetype
     * @property int    filesize
     * @property string key       must be unique across a model's attachments pool
     * @property string group     allows to group attachments
     * @property string title
     * @property string description
     * @property string preview_url
     * @property array  metadata
     * 
     **/

    protected $table = 'attachments';

    protected $fillable = ['uuid', 'url', 'filename', 'filetype', 'filesize', 'title', 'description', 'key', 'group',
     'attachmenttype_codice', 'model_id', 'model_type', 'emission_date', 'docnumber', 'nrecord', 'num_prot', 'num_rep' ];

    protected $hidden = ['model_type', 'model_id','key','group','metadata','title','description','preview_url'];

    //emission_data è la data di protocollazione
    protected $casts = [
        'emission_date' => 'datetime:d-m-Y',
        'created_at' => 'datetime:d-m-Y H:m:s',
    ];
    
    /**
     * Set attribute to date format
     * @param $input
     */
    public function setEmissionDateAttribute($input)
    {
        if($input != '') {
            $this->attributes['emission_date'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['emission_date'] = '';
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getEmissionDateAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return '';
        }
    }
    
    public function setDescription(){
        //se le descrizione è vuota aggiungo quella di defautl
        if (!$this->description){
            $type = ($this->attachmenttype()->first());
            if ($type)
                $this->description = $type->descrizione;
        }        
    }    

    /**
     * Shortcut method to bind an attachment to a model
     *
     * @param string $uuid
     * @param Model  $model   a model that uses HasAttachment
     * @param array  $options filter options based on configuration key `attachments.attributes`
     *
     * @return Attachment|null
     */
    public static function attach($uuid, $model, $options = [])
    {
        /** @var Attachment $attachment */
        $attachment = self::where('uuid', $uuid)->first();
        if ( ! $attachment) {
            return null;
        }       
        $options = Arr::only($options, $attributes);
        $attachment->fill($options);
        return $attachment->model()->associate($model)->save() ? $attachment : null;
    }  

 
    public function createLink($num_prot, $controller = null)
    {
        $this->uuid = $num_prot;
        $this->disk = 'external';
        $this->filepath = '[/doc/@num_prot]='.$num_prot;
        $this->filename = "Collegamento";
        $this->filetype = 'link';
        $this->filesize = 0;
        return $this;
    }

    public function createEmptyFile()
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->disk = '';
        $this->filepath = '';
        $this->filename = '';
        $this->filetype = 'empty';
        $this->filesize = 0;
        return $this;
    }



    /**
     * Creates a file object from a stream
     *
     * @param resource $stream   source stream
     * @param string   $filename the resource filename
     * @param string   $disk     target storage disk
     * @param AttachmentType   $attachmenttype     target storage disk
     * 
     * @return $this|null
     */
    public function loadStream($stream, $disk = null)
    {
        if ($this->attachmenttype_codice == null){
            return null;
        }
        if ( $this->filename == null){
            return null;
        }
        if ($stream === null) {
            return null;
        }        
        $this->disk = $this->disk ?: ($disk ?: Storage::getDefaultDriver());
        $driver = Storage::disk($this->disk);
        
        $this->filepath = $this->filepath ?: ($this->getStorageDirectory() . $this->getPartitionDirectory() . $this->getDiskName());
  
        $driver->put(
            $this->filepath,
            base64_decode($stream)
        );

        $this->filesize = $driver->size($this->filepath);
        $this->filetype = $driver->mimeType($this->filepath);
        return $this;
    }


    /**
     * Creates a file object from a stream
     *
     * @param resource $stream   source stream
     * @param string   $filename the resource filename
     * @param string   $disk     target storage disk
     * @param AttachmentType   $attachmenttype     target storage disk
     * 
     * @return $this|null
     */
    public function fromStream($stream, $filename, $attachmenttype, $disk = null)
    {
        if ($stream === null) {
            return null;
        }
        $this->attachmenttype()->associate($attachmenttype);
        $this->disk = $this->disk ?: ($disk ?: Storage::getDefaultDriver());
        $driver = Storage::disk($this->disk);
        $this->attachmenttype_codice = $attachmenttype->codice;
        $this->filename = $filename;
        $this->filepath = $this->filepath ?: ($this->getStorageDirectory() . $this->getPartitionDirectory() . $this->getDiskName());
    
        $driver->put(
            $this->filepath,
            base64_decode($stream)
        );

        $this->filesize = $driver->size($this->filepath);
        $this->filetype = $driver->mimeType($this->filepath);
        return $this;
    }


    /*
     * Model handling
     */
    
    /**
     * Relationship: model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Get the user record associated with the convenzione.
     */
    public function attachmenttype()
    {
        return $this->belongsTo(AttachmentType::class, 'attachmenttype_codice', 'codice');
    }
    
    public function scopeAttachmentType($query)
    {
        return $query->with('attachmenttype');
    }

    public function getExtensionAttribute()
    {
        return $this->getExtension();
    }

    public function getPathAttribute()
    {
        return pathinfo($this->filepath, PATHINFO_DIRNAME);
    }



    /**
     * Generates a partition for the file.
     * return convenzione1/ per il modello convenzione codice 1 
     *
     * @return mixed
     */
    protected function getPartitionDirectory()
    {
        //la partizione è data dal codice dell'oggetto a cui è collegata        
        return str_replace('App\\','', $this->model_type) .'_'. $this->model_id . '/';
    }

    /**
     * Generates a disk name from the supplied file name.
     */
    protected function getDiskName()
    {
        if ($this->filepath !== null) {
            return $this->filepath;
        }
        $ext = strtolower($this->getExtension());

        if (empty($attachment->uuid)) {
            $this->uuid = Uuid::uuid4()->toString();
        }
        //il nome è dato dal codice del tipo    
        $codice = $this->attachmenttype_codice;
        if ($this->attachmenttype)         
            $codice = $this->attachmenttype->codice;          

        $name = $codice.'_'.str_replace('.', '', $this->uuid);
        return $this->filepath = $ext !== null ? $name . '.' . $ext : $name;
    }

    /**
     * Get a metadata value by key with dot notation
     *
     * @param string $key     The metadata key, supports dot notation
     * @param mixed  $default The default value to return if key is not found
     *
     * @return array|mixed
     */
    public function metadata($key, $default = null)
    {
        if (is_null($key)) {
            return $this->metadata;
        }
        return Arr::get($this->metadata, $key, $default);
    }

    /**
     * Returns the file extension.
     */
    public function getExtension()
    {
        return FileHelper::extension($this->filename);        
    }

    /**
     * Define the internal storage path, override this method to define.
     */
    protected function getStorageDirectory()
    {
        return 'attachments/';
    }

    /**
     * Returns true if the storage engine is local.
     *
     * @return bool
     */
    protected function isLocalStorage()
    {
        return $this->disk == 'local';
    }



    protected function deleteFile()
    {
        if ($this->filetype!='empty'){
            Storage::delete($this->filepath);
        }
        //$this->deleteEmptyDirectory($this->path);
    }



    /**
     * Setup behaviors
     */
    protected static function boot()
    {
        parent::boot();
        Attachment::observe(new UserActionsObserver());

        static::deleting(function ($attachment) {
            /** @var Attachment $attachment */
            $attachment->deleteFile();
        });        
    }

    public static function isFirmataEntrambi($data){
        if ($data['attachmenttype_codice']=='CONV_FIRM_ENTRAMBI')
            return true;
        return false;
    }

}
