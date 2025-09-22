<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'item_id',
        'sending_postcode',
        'sending_address',
        'sending_building'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}