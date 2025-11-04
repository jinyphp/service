<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enable',
        'pos',
        'code',
        'title',
        'description',
        'image',
        'color',
        'icon',
        'parent_id',
        'meta_title',
        'meta_description',
        'manager'
    ];

    protected $casts = [
        'enable' => 'boolean',
        'pos' => 'integer',
        'deleted_at' => 'datetime'
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}