<?php
namespace tests\fixture;


//TODO: php 8.1 native backed enum

final class IntEnum
   {
      public const FOO  = 1;
      public const BAR  = 2;
      
      
      public int $value;
      
      
      
      private function __construct(int $val)
         {
            if(!in_array($val, self::cases()))
               throw new \InvalidArgumentException('Invalid value: '.$val);
            
            $this->value = $val;
         }
      
      
      public static function cases(): array
         {
            return [
               self::FOO,
               self::BAR
            ];
         }
      
      
      public static function from(int $val): self
         {
            return new self($val);
         }
      
      public static function tryFrom(int $val): ?self
         {
            try {$inst = self::from($val);}
            catch(\Exception $ex) {$inst = null;}
            return $inst;
         }
   }
