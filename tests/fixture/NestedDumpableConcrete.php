<?php
namespace tests\fixture;
use dp\dumpable;


class NestedDumpableConcrete implements dumpable\IDumpable
   {
      use dumpable\TDumpable;
      
      
      #[dumpable\attributes\PropDumpable(class:UnhintedScalarsDumpable::class)]
      public UnhintedScalarsDumpable $nested;
      
      
      public function __construct(UnhintedScalarsDumpable $nested)
         {
            $this->nested = $nested;
         }
   }
