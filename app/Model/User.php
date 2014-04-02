<?php

class User extends AppModel
{
    public $name = 'User';
    public $hasMany = 'Comment';
    public $hasOne = 'Pref';
}
