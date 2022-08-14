<?php

namespace Mesak\LaravelOpisValidator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Mesak\LaravelOpisValidator\Validation\OpisValidator;

class JsonSchemaRequest extends FormRequest
{
  protected $extendValidatorMessage =  false;
  public function __construct()
  {
    Validator::resolver(function ($translator, $data, $rules, $messages) {
      //CustomValidatorFactory
      $opisValidator  = new OpisValidator($translator, $data, $rules, $messages);
      if ($this->extendValidatorMessage) {
        $opisValidator->useValidatorMessage();
      }
      return $opisValidator;
    });
  }
}
