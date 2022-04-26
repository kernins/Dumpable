<?php
namespace dp\dumpable\attributes;
use Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class PropAbstract
   {
      //TODO: php8.1 declare readonly
      public bool       $isPublic;
      public ?string    $dumpAs;
   
      protected bool    $isCollection;
      protected ?string $collectionClass = null;
      
      
      
      public function __construct(bool $public=true, ?string $dumpAs=null, bool|string $collection=false)
         {
            $this->isPublic = $public;
            $this->dumpAs = $dumpAs;
            
            if(is_string($collection))
               {
                  try 
                     {
                        $rc = new \ReflectionClass($collection);
                        if(!$rc->implementsInterface(\ArrayAccess::class))
                           throw new \DomainException('Collection class must implement \\ArrayAccess interface');
                        $this->collectionClass = $collection;
                     }
                  catch(\ReflectionException $ex)
                     {
                        throw new \InvalidArgumentException(
                           'Invalid collection class hint given: '.$ex->getMessage(),
                           $ex->getCode(),
                           $ex
                        );
                     }
               }
            $this->isCollection = !empty($collection);
         }
      

      public function dump($value)
         {
            if($this->isCollection)
               {
                  //NB: not enforcing is_array | instanceof $this->collectionClass
                  //as this will introduce an artificial limiting factor for cases
                  //where only dump() is required and restore() is not used
                  if(!is_iterable($value)) throw new \InvalidArgumentException(
                     'Iterable expected, but got a '.gettype($value)
                  );
                  
                  $ret = [];
                  foreach($value as $k=>$v) $ret[$k] = $this->dumpValue($v);
                  return $ret;
               }
            return $this->dumpValue($value);
         }
      
      abstract protected function dumpValue($val);
      
      
      public function restore($value)
         {
            if($this->isCollection)
               {
                  if(!is_iterable($value)) throw new \InvalidArgumentException(
                     'Iterable expected, but got a '.gettype($value)
                  );
                  
                  $ret = empty($this->collectionClass)? [] : new $this->collectionClass;
                  foreach($value as $k=>$v) $ret[$k] = $this->restoreValue($v);
                  return $ret;
               }
            return $this->restoreValue($value);
         }
      
      abstract protected function restoreValue($val);
   }
