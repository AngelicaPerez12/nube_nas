<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atributos por defecto.
     */
    protected $attributes = [
        'storage_limit_mb' => 1024,
    ];

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'grupo_id',
        'permiso',
        'storage_limit_mb',
    ];

    /**
     * Atributos ocultos al serializar.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión automática de tipos de datos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'storage_limit_mb' => 'integer',
        ];
    }

    /**
     * Relación: un usuario pertenece a un grupo (departamento).
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * Calcula los bytes utilizados en el almacenamiento privado del usuario.
     */
    public function espacioUsadoBytes(): int
    {
        $carpetaPrivada = 'usuario_'.$this->id;

        if (! Storage::disk('nas')->exists($carpetaPrivada)) {
            return 0;
        }

        $totalBytes = 0;

        foreach (Storage::disk('nas')->allFiles($carpetaPrivada) as $archivo) {
            $totalBytes += Storage::disk('nas')->fileSize($archivo);
        }

        return $totalBytes;
    }

    /**
     * Retorna el límite asignado en bytes.
     */
    public function limiteEspacioBytes(): int
    {
        $limiteMb = $this->storage_limit_mb > 0 ? $this->storage_limit_mb : 1024;

        return $limiteMb * 1024 * 1024;
    }

    /**
     * Retorna el porcentaje de uso del espacio asignado.
     */
    public function porcentajeUso(): float
    {
        $limite = $this->limiteEspacioBytes();

        if ($limite <= 0) {
            return 0.0;
        }

        $porcentaje = ($this->espacioUsadoBytes() / $limite) * 100;

        return round(min(100.0, max(0.0, $porcentaje)), 1);
    }

    /**
     * Formatea una cantidad de bytes a una unidad legible (B, KB, MB, GB).
     */
    public function formatearBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        $valor = (float) $bytes;
        while ($valor >= 1024 && $i < count($unidades) - 1) {
            $valor /= 1024;
            $i++;
        }

        return round($valor, 1).' '.$unidades[$i];
    }

    /**
     * Retorna el espacio usado en formato legible (ej. 12.5 MB).
     */
    public function espacioUsadoTexto(): string
    {
        return $this->formatearBytes($this->espacioUsadoBytes());
    }

    /**
     * Retorna el límite de espacio en formato legible (ej. 1 GB).
     */
    public function limiteEspacioTexto(): string
    {
        return $this->formatearBytes($this->limiteEspacioBytes());
    }
}
