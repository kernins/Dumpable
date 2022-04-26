<?php
namespace tests\fixture;
use dp\dumpable;


class HintedScalarDumpable implements dumpable\IDumpable
   {
      use dumpable\TDumpable;
      
      
      #[dumpable\attributes\PropScalar]
      public $scalar;
      
      
      public function __construct($val)
         {
            $this->scalar = $val;
         }
   }
