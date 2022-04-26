<?php
namespace dp\dumpable\attributes;
use Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
final class PropEphemeral extends PropAbstract
   {
      public function __construct()
         {
            //no args here, this is just a marker-handler
            parent::__construct();
         }
   
      
      //TODO: php8.1 'never' return type
      protected function dumpValue($val): void
         {
            throw new \LogicException('Ephemeral properties must not be dumped');
         }
         
      //TODO: php8.1 'never' return type
      protected function restoreValue($val): void
         {
            throw new \LogicException('Ephemeral properties must not be restored');
         }
   }
