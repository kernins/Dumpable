<?php
namespace dp\dumpable;


class DumpableContainer implements IDumpable
   {
      use TDumpable {
         dump as baseDump;
         restore as baseRestore;
      }
      
      
      protected string     $className;
      
      #[attributes\PropArray(public:true)]
      protected array      $state;
      
      #[attributes\PropEphemeral]
      protected IDumpable  $dumpable;
      
      
      
      public function __construct(IDumpable $dumpable)
         {
            $this->dumpable = $dumpable;
            $this->className = $dumpable::class;
         }
      
      
      public static function restore(array $dump): static
         {
            /* @var $inst self */
            $inst = self::baseRestore($dump);
            
            $inst->dumpable = $inst->className::restore($inst->state);
            $inst->state = []; //clean-up
            
            return $inst;
         }
      
      public function dump(int $modeFlags=0): array
         {
            try
               {
                  $this->state = $this->dumpable->dump($modeFlags);
                  return $this->baseDump($modeFlags);
               }
            finally
               {
                  $this->state = []; //clean-up
               }
         }
      
         
      protected function publicDumpDecorate(array $dump): array
         {
            return $dump['state'];
         }
   }
