<?php
namespace tests\fixture;
use dp\dumpable;


class NestedDumpableAbstract implements dumpable\IDumpable
   {
      use dumpable\TDumpable;
      
      
      #[dumpable\attributes\PropDumpable]
      public dumpable\IDumpable $nested;
      
      
      public function __construct(dumpable\IDumpable $nested)
         {
            $this->nested = $nested;
         }
   }
