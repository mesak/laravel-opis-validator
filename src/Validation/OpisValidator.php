<?php

namespace Mesak\LaravelOpisValidator\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator as BaseValidator;

use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Errors\ValidationError;


class OpisValidator extends BaseValidator
{
  protected $validator;
  protected $schema;
  protected $extendValidatorMessage = false;
  /**
   * Create a new Validator instance.
   *
   * @param  \Illuminate\Contracts\Translation\Translator  $translator
   * @param  array  $data
   * @param  array  $rules
   * @param  array  $messages
   * @param  array  $customAttributes
   * @return void
   */
  public function __construct(
    Translator $translator,
    array $data,
    array $rules,
    array $messages = []
  ) {
    $this->validator = new Validator();
    $this->dotPlaceholder = Str::random();

    $this->initialRules = $rules;
    $this->translator = $translator;
    $this->customMessages = $messages;
    $this->data = $data;
    $this->setRules($rules);
  }


  public function useValidatorMessage()
  {
    $this->extendValidatorMessage = true;
    return $this;
  }

  /**
   * Set the validation rules.
   *
   * @param  array  $rules
   * @return $this
   */
  public function setRules(array $rules)
  {
    $objectRules =  Helper::toJSON($rules);
    $this->schema = $this->validator->loader()->loadObjectSchema($objectRules); //change to object schema
    $this->rules = $this->getSchemaDataKeys($this->schema->info());
    return $this;
  }

  /**
   * get opi json schema.
   *
   * @param  array  $rules
   * @return $this
   */
  public function getSchema()
  {
    return $this->schema;
  }

  /**
   * get data with json format data
   *
   * @param  array  $rules
   * @return $this
   */
  public function getJsonData()
  {
    return Helper::toJSON($this->getData());
  }

  /**
   * Run the validator's rules against its data.
   *
   * @return ValidationResult
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function validateResult()
  {
    return $this->validator->validate($this->getJsonData(), $this->getSchema());
  }

  /**
   * Determine if the data passes the validation rules.
   *
   * @return bool
   */
  public function passes()
  {
    $this->messages = new MessageBag;

    $this->failedRules = [];

    $result = $this->validateResult();

    if ($result->hasError()) {
      $this->addFailureWithError($result->error());
    }
    foreach ($this->after as $after) {
      $after();
    }
    return $this->messages->isEmpty();
  }

  /**
   * add Failure with ValidationError
   *
   * @param  ValidationError  $error
   * @return void
   */
  public function addFailureWithError(ValidationError $error)
  {
    $formatter = new ErrorFormatter();
    foreach ($formatter->getLeafErrors($error) as $error) {

      $rule = $formatter->formatKeywordToRule($error);
      $lowerRule = Str::studly($rule);
      // $parameters = array_values($error->args());
      $parameters = in_array($lowerRule, $this->sizeRules) ? array_values($error->args()) :  $error->args();

      $attribute = str_replace(
        [$this->dotPlaceholder, '__asterisk__'],
        ['.', '*'],
        $formatter->getErrorAttribute($error)
      );

      $message = $this->makeReplacements(
        $this->getMessage($attribute, $rule),
        $attribute,
        $rule,
        $parameters
      );

      // dd($parameters, $attribute, $rule);
      if ($this->extendValidatorMessage && !$this->getInlineMessage($attribute, $rule)) {
        $message = $formatter->formatErrorMessage($error);
      }
      $this->messages->add($attribute, $message);

      $this->failedRules[$attribute][$rule] = $parameters;
    }
  }


  protected function getSchemaDataKeys($info)
  {
    $data  = $info->data();
    $result = [];
    if (property_exists($data, 'properties')) {
      $result = $this->getSchemaProperties($data->properties);
    }
    return Arr::dot($result);
  }

  protected function getSchemaProperties($properties)
  {
    $result = [];
    if ($properties instanceof \stdClass) {
      foreach ((array) $properties as $key => $value) {
        // $result[$key] = [];
        $result[$key] = $value->type ?? '';
        if (property_exists($value, 'properties')) {
          $result[$key] = $this->getSchemaProperties($value->properties);
        }
      }
    }
    return $result;
  }
}
