<?php
namespace dp\dumpable\attributes;
use Attribute, dp\dumpable;


#[Attribute(Attribute::TARGET_PROPERTY)]
final class PropDumpable extends PropAbstract
   {
      protected string  $className;
      protected int     $nestedDumpMode = 0;
      
      
      
      public function __construct(string $class, bool $public=true, ?string $dumpAs=null, bool|string $collection=false)
         {
            try 
               {
                  $rc = new \ReflectionClass($class);
                  if(!$rc->implementsInterface(dumpable\IDumpable::class))
                     throw new \DomainException('Target class must implement IDumpable interface');
                  $this->className = $class;
               }
            catch(\ReflectionException $ex)
               {
                  throw new \InvalidArgumentException(
                     'Invalid target class given: '.$ex->getMessage(),
                     $ex->getCode(),
                     $ex
                  );
               }
         
            parent::__construct($public, $dumpAs, $collection);
         }
      
      public function setNestedDumpMode(int $mode): self
         {
            $this->nestedDumpMode = $mode;
            return $this;
         }
   
   
      /**
       * @param Dumpable\IDumpable $val
       * @return array|null               null is allowed only in public mode, see IDumpable
       * @throws \DomainException
       */
      protected function dumpValue($val): ?array
         {
            if(!($val instanceof dumpable\IDumpable))
               throw new \DomainException('Expected an instance of IDumpable, got '.gettype($val));
         
            return $val->dump($this->nestedDumpMode);
         }
         
      protected function restoreValue($val): dumpable\IDumpable
         {
            return $this->className::restore($val);
         }
   }
