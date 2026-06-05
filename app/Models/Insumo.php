<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Insumo extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'Insumos';

    protected $fillable = [
        'id_insumo',
        'nombre',
        'tipo',
        'cantidad',
        'cantidad_minima',
        'caducidad',
        'proveedor',
        'precio_unitario',
    ];

    public $timestamps = true;

    /**
     * Genera el siguiente ID autoincremental consultando el último registro.
     */
    public static function generarId(): int
    {
        $ultimo = self::orderBy('id_insumo', 'desc')->first();
        return $ultimo ? ($ultimo->id_insumo + 1) : 1;
    }
}
