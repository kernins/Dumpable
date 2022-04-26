<?php
namespace dp\dumpable\attributes;
use Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
final class PropScalar extends PropAbstract
   {
      protected function dumpValue($val): int|float|string|bool
         {
            if(!is_scalar($val)) throw new \InvalidArgumentException(
               'Value must be scalar, '.gettype($val).' given'
            );
            
            return $val;
         }
         
      protected function restoreValue($val): int|float|string|bool
         {
            if(!is_scalar($val)) throw new \InvalidArgumentException(
               'Value must be scalar, '.gettype($val).' given'
            );
         
            return $val;
         }
   }
