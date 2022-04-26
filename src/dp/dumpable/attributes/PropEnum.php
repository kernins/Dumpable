<?php
namespace dp\dumpable\attributes;
use Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
final class PropEnum extends PropAbstract
   {
      protected string  $className;
      
      
      
      public function __construct(string $class, bool $public=true, ?string $dumpAs=null, bool|string $collection=false)
         {
            //TODO: php 8.1 validate $enum is instanceof BackedEnum
            
            $this->className = $class;
            parent::__construct($public, $dumpAs, $collection);
         }
   
   
      protected function dumpValue($val): int|string
         {
            //TODO: php 8.1 expect instanceof BackedEnum
         
            return $val->value;
         }
         
      protected function restoreValue($val) //TODO: return BackedEnum
         {
            return $this->className::from($val);
         }
   }
