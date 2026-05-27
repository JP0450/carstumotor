<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformeFinanciero extends Model
{
    use HasFactory;

    protected $table = 'informes_financieros';

    protected $fillable = [
        'user_id',
        'tipo',
        'periodo',
        'nombre_archivo',
        'ruta',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
