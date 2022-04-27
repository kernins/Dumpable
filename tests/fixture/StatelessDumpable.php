<?php
namespace tests\fixture;
use dp\dumpable;


class StatelessDumpable implements dumpable\IDumpable
   {
      use dumpable\TDumpable;
   }
