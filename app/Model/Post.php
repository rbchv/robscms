<?php

class Post extends AppModel
{
    public $name = 'Post';
    public $hasMany = 'Comment';
}
