<?php

namespace Mesak\LaravelOpisValidator\Validation;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Errors\ErrorFormatter as BaseErrorFormatter;

class ErrorFormatter extends BaseErrorFormatter
{
  public function formatKeywordToRule(ValidationError $error)
  {
    $keyword = $error->keyword();
    switch ($keyword) {
      case 'maximum':
        return 'max';
      case 'minimum':
        return 'min';
      case 'multipleOf':
        return 'multiple';
      case 'maxLength':
        return 'size';
      default:
        return $keyword;
    }
  }

  /**
   * @param ValidationError $error
   * @return iterable|ValidationError[]
   */
  public function getLeafErrors(ValidationError $error): iterable
  {
    if ($subErrors = $error->subErrors()) {
      foreach ($subErrors as $subError) {
        yield from $this->getLeafErrors($subError);
      }
    } else {
      yield $error;
    }
  }

  public function getErrorAttribute(ValidationError $error): string
  {
    $path = $error->data()->fullPath();
    if (!$path) {
      return '';
    }
    return implode('.', $path);
  }
}
