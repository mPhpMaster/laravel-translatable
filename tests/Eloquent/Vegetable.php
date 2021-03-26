<?php

namespace mPhpMaster\Translatable\Tests\Eloquent;

use mPhpMaster\Translatable\Contracts\Translatable as TranslatableContract;
use mPhpMaster\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Vegetable extends Eloquent implements TranslatableContract
{
    use Translatable;

    protected $primaryKey = 'identity';

    public $translationForeignKey = 'vegetable_identity';

    public $translatedAttributes = ['name'];

    protected $fillable = ['quantity'];

    public $localeKey;

    public $translationModel;
}
