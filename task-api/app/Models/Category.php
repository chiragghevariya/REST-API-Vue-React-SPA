<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'color',
        'user_id',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The user that owns this category.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tasks that belong to this category.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Eager-load the count of tasks for a set of categories.
     * Usage: Category::withTaskCount()->get()
     */
    public function scopeWithTaskCount($query)
    {
        return $query->withCount('tasks');
    }
}
