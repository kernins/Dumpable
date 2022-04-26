<?php
namespace dp\dumpable;


trait TDumpable
   {
      public function dump(int $modeFlags=0): array
         {
            $isPublicMode = $modeFlags & IDumpable::DF_MODE_PUBLIC;
            $skipDefault = $modeFlags & IDumpable::DF_SKIP_DEFAULT;
            
            $ret = [];
            foreach((new \ReflectionObject($this))->getProperties() as $prop)
               {
                  /* @var $prop \ReflectionProperty */
                  
                  $dHndl=self::getDumpHandlerForProp($prop);
                  if($dHndl instanceof attributes\PropEphemeral) continue;
                  if($dHndl instanceof attributes\PropDumpable) $dHndl->setNestedDumpMode($modeFlags);
                  
                  if($isPublicMode)
                     {
                        if(!empty($dHndl))
                           {
                              if(!$dHndl->isPublic) continue;
                           }
                        //prop visibility matters only for no-handler-attached props
                        elseif(!$prop->isPublic()) continue;
                     }
                  
                  $prop->setAccessible(true); //php < 8.1
                  $val = $prop->getValue($this);
                  
                  if($skipDefault && $prop->hasDefaultValue() && ($prop->getDefaultValue()===$val)) continue;
                  
                  $ret[$dHndl?->dumpAs ?? $prop->name] = match(true) {
                     ($val === null)   => null,
                     !empty($dHndl)    => $dHndl->dump($val),
                     is_scalar($val)   => $val,
                     default           => throw new \LogicException('Non-scalar dumpable properties must have a handler attached')
                  };
               }
            
            return $isPublicMode? $this->publicDumpDecorate($ret) : $ret;
         }
         
      protected function publicDumpDecorate(array $dump): array
         {
            return $dump;
         }
      
      
      public static function restore(array $dump): static
         {
            $rc = new \ReflectionClass(static::class);
            $inst = $rc->newInstanceWithoutConstructor();
            
            foreach($rc->getProperties() as $prop)
               {
                  /* @var $prop \ReflectionProperty */
               
                  $dHndl=static::getDumpHandlerForProp($prop);
                  if($dHndl instanceof attributes\PropEphemeral) continue;
                  $dumpedAs = $dHndl?->dumpAs ?? $prop->name;
                  
                  if(array_key_exists($dumpedAs, $dump))
                     {
                        $prop->setAccessible(true); //php < 8.1
                        $val = $dump[$dumpedAs];
                        
                        try
                           {
                              $prop->setValue(
                                 $inst,
                                 ($val!==null) && !empty($dHndl)? $dHndl->restore($val) : $val
                              );
                           }
                        catch(\TypeError $ex)
                           {
                              throw new \InvalidArgumentException(
                                 'Invalid data for '.$prop->name.' ['.$dumpedAs.'] property: '.$ex->getMessage(),
                                 $ex->getCode(),
                                 $ex
                              );
                           }
                     }
                  elseif(!$prop->hasDefaultValue())
                     {
                        throw new \InvalidArgumentException(
                           'Given dump is missing mandatory property '.$prop->name.' ['.$dumpedAs.']'
                        );
                     }
               }
            return $inst;
         }
      
      
      final protected static function getDumpHandlerForProp(\ReflectionProperty $prop): ?attributes\PropAbstract
         {
            foreach($prop->getAttributes() as $attr)
               {
                  /* @var $attr \ReflectionAttribute */
                  try {
                     $arc = new \ReflectionClass($attr->getName());
                     if(!$arc->isSubclassOf(attributes\PropAbstract::class)) continue;
                  } catch(\ReflectionException $ex) {continue;}
                        
                  return $attr->newInstance();
               }
            return null;
         }
   }
