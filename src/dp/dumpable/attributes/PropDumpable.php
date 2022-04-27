<?php
namespace dp\dumpable\attributes;
use Attribute, dp\dumpable;


#[Attribute(Attribute::TARGET_PROPERTY)]
final class PropDumpable extends PropAbstract
   {
      protected ?string $className = null;
      protected int     $nestedDumpMode = 0;
      
      
      
      public function __construct(?string $class=null, bool $public=true, ?string $dumpAs=null, bool|string $collection=false)
         {
            if($class !== null)
               {
                  self::_validateClassName($class);
                  $this->className = $class;
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
            
            $dump = $val->dump($this->nestedDumpMode);
            if(empty($this->className) && !($this->nestedDumpMode & dumpable\IDumpable::DF_MODE_PUBLIC))
               {
                  //class-unhinted private dump
                  $ret = ['__class__' => $val::class];
                  if(!empty($dump)) $ret['state'] = $dump; //dumpable may be stateless/all-defaults
                  return $ret;
               }
            else return $dump; //class-hinted or public dump
         }
      
      protected function restoreValue($val): dumpable\IDumpable
         {
            if(empty($this->className))
               {
                  $class = $val['__class__'] ?? null;
                  $val = $val['state'] ?? []; //empty-array-fallback is for stateless/all-defaults dumpables
               }
            else $class = $this->className;
         
            if(empty($class)) throw new \InvalidArgumentException(
               'Given value is missing required target class FQN specification'
            );
            
            self::_validateClassName($class);
            return $class::restore($val);
         }
      
      
      private static function _validateClassName(string $fqn): void
         {
            try 
               {
                  $rc = new \ReflectionClass($fqn);
                  if(!$rc->implementsInterface(dumpable\IDumpable::class))
                     throw new \DomainException('Target class must implement IDumpable interface');
               }
            catch(\ReflectionException $ex)
               {
                  throw new \InvalidArgumentException(
                     'Invalid target class given: '.$fqn.': '.$ex->getMessage(),
                     $ex->getCode(),
                     $ex
                  );
               }
         }
   }
