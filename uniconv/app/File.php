<?php 

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{

    protected $fillable = [
        'filename',
        'filetype',
        'value',        
    ];

    public function __construct($disk = 'local')
    {        
        $this->disk = $disk;
    }

    public function save()
    {
        return $this->storeToDisk();
    }

    public function getName() {
        return 'convenzione';
    }
 
    protected function storeToDisk()
    {
        Storage::disk($this->disk)->put(
            $this->getName(),
            base64_decode($this->value)
        );
    }
}