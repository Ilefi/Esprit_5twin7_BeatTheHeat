<?php

namespace App\Models;

use Database\Factories\EmissionFactorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmissionFactor extends Model
{
    /** @use HasFactory<EmissionFactorFactory> */
    use HasFactory;

    protected $fillable = ['name', 'category', 'unit', 'value', 'source'];

    protected function casts(): array
    {
        return [
            'value' => 'float',
        ];
    }
}
