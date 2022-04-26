<?php
namespace tests\fixture;
use dp\dumpable;


class UnhintedScalarsDumpable implements dumpable\IDumpable
   {
      use dumpable\TDumpable;
      
      
      public function __construct(
         public int        $pubInt,
         public float      $pubFloat,
         public string     $pubString,
         public bool       $pubBool,      
         
         protected int     $protInt,
         protected float   $protFloat,
         protected string  $protString,
         protected bool    $protBool,
               
         private int       $privInt,
         private float     $privFloat,
         private string    $privString,
         private bool      $privBool
      ) {}
   }
