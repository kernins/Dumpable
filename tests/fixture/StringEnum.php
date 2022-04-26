<?php
namespace tests\fixture;


//TODO: php 8.1 native backed enum

final class StringEnum
   {
      public const FOO  = 'foo';
      public const BAR  = 'bar';
      
      
      public string $value;
      
      
      
      private function __construct(string $val)
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
      
      
      public static function from(string $val): self
         {
            return new self($val);
         }
      
      public static function tryFrom(string $val): ?self
         {
            try {$inst = self::from($val);}
            catch(\Exception $ex) {$inst = null;}
            return $inst;
         }
   }
