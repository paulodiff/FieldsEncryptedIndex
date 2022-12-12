<?php

/**
 * FieldsEncryptedIndexException
 * Custom Exception
 * 
 */

namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Exception;

class FieldsEncryptedIndexException extends Exception  {

    protected $model;
    public $enc_fields;
    protected $rtService;

	public function report()
    {
        \Log::debug('User not found');
    }
    
}