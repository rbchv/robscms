<?php

class Pref extends AppModel
{
    public $name = 'Pref';
    public $belongsTo = 'User';
    public $primaryKey = 'user_id';
}
