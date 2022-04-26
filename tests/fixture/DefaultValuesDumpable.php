<?php
namespace tests\fixture;
use dp\dumpable;


class DefaultValuesDumpable implements dumpable\IDumpable
   {
      use dumpable\TDumpable;
      
      public int     $int     = 1;
      public float   $float   = 0.4;
      public string  $string  = 'foo';
      public bool    $bool    = false;
      public ?string $nullStr = null;
   }
