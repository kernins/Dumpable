<?php
namespace dp\dumpable\attributes;
use Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
final class PropArray extends PropAbstract
   {
      protected function dumpValue($val): array
         {
            if(!is_array($val)) throw new \InvalidArgumentException(
               'Value must be an array, '.gettype($val).' given'
            );
            
            return $val;
         }
         
      protected function restoreValue($val): array
         {
            if(!is_array($val)) throw new \InvalidArgumentException(
               'Value must be an array, '.gettype($val).' given'
            );
         
            return $val;
         }
   }
