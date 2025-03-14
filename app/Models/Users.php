<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
Use App\Role;

class Users extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'users';
    protected $fillable = [
        'id','kodeusers','nik', 'name','password','email_verified_at','roles','region','unit','jabatan','tglmasuk','masakerja','foto','remember_token'
    ];
  
}