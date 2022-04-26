<?php
namespace dp\dumpable;


interface IDumpable
   {
      const DF_MODE_PUBLIC    = 0b00000001;
      const DF_SKIP_DEFAULT   = 0b10000000;
   
   
      /**
       * Dump instance data to array
       * By default, with no DF_MODE_* flags passed, must perform full dump suitable for restore()
       * 
       * NB: there are cases where it makes sense for public dump() to return null,
       *    e.g. subject's data is conditionally public (and condition is internal to subject class)
       * NB: there are cases where it makes sense to return null even for private (full) dump()
       *    e.g. stateless class enclosed in DumpableContainer
       * 
       * @param int $modeFlags   Bitmask of DF_* flags
       * @return array|null      NULL is allowed only in public mode
       */
      public function dump(int $modeFlags=0): ?array;
      
      public static function restore(array $dump): static;
   }
