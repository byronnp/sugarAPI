<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagenes_Avaluo extends Model
{
    use HasFactory;
    protected $connection = 'sugar_dev';
    protected $table = 'cba_imagenes_avaluo_cba_avaluos_c';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['cba_imagenes_avaluo_cba_avaluoscba_avaluos_ida',
        'cba_imagenes_avaluo_cba_avaluoscba_imagenes_avaluo_idb',
        'date_modified', 'deleted'];
    /**
     * @var mixed
     */

    /**
     * @var mixed|string
     */

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->setConnection(get_connection());
    }
}
