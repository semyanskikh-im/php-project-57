<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status_id',
        'created_by_id',
        'assigned_to_id',
    ];

    // Связь со статусом
    public function status()
    {
        return $this->belongsTo(TaskStatus::class);
    }

    // Связь с создателем
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // Связь с исполнителем
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
