<?php declare(strict_types=1);
namespace tests;
use PHPUnit\Framework\TestCase;
use dp\dumpable;


final class DumpableTest extends TestCase
   {
      public function testUnhintedScalarDump(): array
         {
            $subj = new fixture\UnhintedScalarsDumpable(
               pubInt: 2,
               pubFloat: 3.4,
               pubString: 'foobar',
               pubBool: false,
               
               protInt: 4,
               protFloat: 5.0,
               protString: 'barfoo',
               protBool: true,
               
               privInt: 0,
               privFloat: 1.6,
               privString: 'baz',
               privBool: false
            );
            
            $pub = [
               'pubInt' => 2,
               'pubFloat' => 3.4,
               'pubString' => 'foobar',
               'pubBool' => false
            ];
            
            $this->assertSame(
               $pub,
               $subj->dump(dumpable\IDumpable::DF_MODE_PUBLIC)
            );
            
            $full = $pub + [
               'protInt' => 4,
               'protFloat' => 5.0,
               'protString' => 'barfoo',
               'protBool' => true,
               'privInt' => 0,
               'privFloat' => 1.6,
               'privString' => 'baz',
               'privBool' => false
            ];
            
            $this->assertSame(
               $full,
               $subj->dump()
            );
            
            return $full;
         }
         
      /**
       * @depends testUnhintedScalarDump
       */
      public function testUnhintedScalarRestore(array $dump): void
         {
            $this->assertSame(
               $dump,
               fixture\UnhintedScalarsDumpable::restore($dump)->dump()
            );
         }
      
      
      public function testDumpNonscalarMustHaveHandler(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               public array $pubArr = [1, 2, 3];
            });
            
            $this->expectException(\LogicException::class);
            $subj->dump();
         }
      
      
      public function testRestoreSameTypeDifferentInstance(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               public int $pubInt = 5;
            });
            
            $rSubj = $subj::restore($subj->dump());
            $this->assertInstanceOf($subj::class, $rSubj);
            $this->assertNotSame($subj, $rSubj);
         }
         
      public function testRestoreMissingProps(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               public int $required;
            });
            
            $this->expectException(\InvalidArgumentException::class);
            $subj::restore([]);
         }
         
      /**
       * @dataProvider restoreTypeMismatchProvider
       */
      public function testRestoreTypeMismatch($value): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               public int $pubInt = 5;
            });
            
            $this->expectException(\InvalidArgumentException::class);
            $subj::restore(['pubInt' => $value]);
         }
         
      public function restoreTypeMismatchProvider(): array
         {
            return [
               'string' => ['nonnumericstring'],
               'null'   => [null]
            ];
         }
      
      
      public function testNullableProp(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               public ?int $nullable = null;
            });
            
            $this->assertSame([
               'nullable' => null
            ], $subj->dump());
            $this->assertSame(
               5,
               $subj::restore(['nullable' => 5])->nullable
            );
            
            $subj->nullable = 3;
            $this->assertSame([
               'nullable' => 3
            ], $subj->dump());
            $this->assertSame(
               null,
               $subj::restore(['nullable' => null])->nullable
            );
         }
         
      public function testStatelessDumpable(): void
         {
            $subj = new fixture\StatelessDumpable();
            
            $this->assertSame([], $subj->dump());
            $this->assertInstanceOf($subj::class, $subj::restore([]));
         }
         
         
      public function testDefaultValueDumpSkip()
         {
            $subj = new fixture\DefaultValuesDumpable();
            
            $this->assertNotEmpty($subj->dump());
            $this->assertEmpty($subj->dump(dumpable\IDumpable::DF_SKIP_DEFAULT));
            
            $subj->int = 555777111;
            $subj->nullStr = 'foonotnull';
            
            $this->assertSame([
               'int' => 555777111,
               'nullStr' => 'foonotnull'
            ], $subj->dump(dumpable\IDumpable::DF_SKIP_DEFAULT));
         }
         
      public function testDefaultValueRestoreFallback(): void
         {
            $subj = new fixture\DefaultValuesDumpable();
            $this->assertNotEmpty($subj->dump());
            
            $this->assertSame(
               $subj->dump(),
               fixture\DefaultValuesDumpable::restore([])->dump()
            );
         }
      
      
      public function testHintedVisibilityOverride(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropScalar(public:false)]
               public string     $pubString = 'foo';
               
               #[dumpable\attributes\PropScalar(public:true)]
               protected string  $protString = 'bar';
            });
            
            $this->assertSame([
               'protString' => 'bar'
            ], $subj->dump(dumpable\IDumpable::DF_MODE_PUBLIC));
         }
         
      public function testHintedNameOverride(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropScalar(public:false, dumpAs:'overriden')]
               public string     $pubString = 'foo';
            });
            
            $this->assertSame(['overriden'=>'foo'], $subj->dump());
            $this->assertSame('bar', $subj::restore(['overriden'=>'bar'])->pubString);
         }
         
      public function testHintedIgnoreUnrelatedAttrs()
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[\SplStack]
               #[dumpable\attributes\PropScalar(public:true)]
               #[NotAClassAttribute]
               protected string $str = 'foo';
               
               #[UnrelatedAttr]
               public int $int = 1;
            });
         
            $this->assertSame([
               'str' => 'foo',
               'int' => 1
            ], $subj->dump(dumpable\IDumpable::DF_MODE_PUBLIC));
         }
      
      
      public function testHintedCollection(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropScalar(public:true, collection:true)]
               public array $arrayOfScalars = [5, 3.1, 'foo', false];
            });
            
            $arr = ['arrayOfScalars' => $subj->arrayOfScalars];
            $this->assertSame($arr, $subj::restore($arr)->dump());
            
            $this->expectException(\InvalidArgumentException::class);
            $subj::restore(['arrayOfScalars' => 'notiterable']);
         }
         
      public function testHintedCollectionObject(): void
         {
            $arrData = [5, 3.1, 'foo', false];
            $subj = (new class($arrData) implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropScalar(public:true, collection:\ArrayObject::class)]
               public \ArrayObject $arrayOfScalars;
               
               public function __construct(array $arr)
                  {
                     $this->arrayOfScalars = new \ArrayObject($arr);
                  }
            });
            
            $arr = ['arrayOfScalars' => $arrData];
            $this->assertSame($arr, $subj::restore($arr)->dump());
            //$subj::restore($arr)->arrayOfScalars instanceof \ArrayObject is guaranteed by typehint
            
            $this->expectException(\InvalidArgumentException::class);
            $subj::restore(['arrayOfScalars' => 'notiterable']);
         }
         
      public function testHintedCollectionNotIterable(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropScalar(collection:true)]
               public string $string = '';
            });
            
            $this->expectException(\InvalidArgumentException::class);
            $subj->dump();
         }
         
      public function testHintedCollectionInexistentClass()
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropScalar(collection:'InexistentClass')]
               public \ArrayObject $col;
               
               public function __construct()
                  {
                     $this->col = new \ArrayObject([1, 2, 3]);
                  }
            });
            
            $this->expectException(\InvalidArgumentException::class);
            $subj->dump();
         }
         
      public function testHintedCollectionInvalidClass()
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropScalar(collection:\SplMaxHeap::class)]
               public \ArrayObject $col;
               
               public function __construct()
                  {
                     $this->col = new \ArrayObject([1, 2, 3]);
                  }
            });
            
            $this->expectException(\DomainException::class);
            $subj->dump();
         }
         
         
      /**
       * @dataProvider scalarValueProvider
       */
      public function testHintedScalar($val): void
         {
            $subj = new fixture\HintedScalarDumpable($val);
            
            $this->assertSame([
               'scalar' => $val
            ], $subj->dump());
            
            $this->assertSame(
               $val,
               fixture\HintedScalarDumpable::restore(['scalar'=>$val])->scalar
            );
         }
         
      public function scalarValueProvider(): array
         {
            return [
               'int'       => [1],
               'float'     => [0.3],
               'string'    => ['foostring'],
               'boolTrue'  => [true],
               'boolFalse' => [false],
               'null'      => [null] //not-a-scalar, testing nullable case
            ];
         }
         
      public function testHintedScalarNotAScalarDump(): void
         {
            $this->expectException(\InvalidArgumentException::class);
            (new fixture\HintedScalarDumpable(new \SplStack()))->dump();
         }
         
      public function testHintedScalarNotAScalarRestore(): void
         {
            $this->expectException(\InvalidArgumentException::class);
            fixture\HintedScalarDumpable::restore([
               'scalar' => new \SplStack()
            ]);
         }
         
         
      public function testHintedEphemeral(): void
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropEphemeral]
               public string $ephemeral = 'ephemeralString';
            });
            
            //ephemeral props must be ignored
            $this->assertSame([], $subj->dump());
            
            //restore data must be ignored => default value must remain
            $this->assertSame(
               $subj->ephemeral,
               $subj::restore(['ephemeral'=>'notSoEphemeral'])->ephemeral
            );
         }
      
      
      /**
       * @depends testUnhintedScalarDump
       */
      public function testHintedDumpableConcreteNested(array $unhintedScalarsDump)
         {
            $subj = (new class($unhintedScalarsDump) implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropDumpable(class:fixture\NestedDumpableConcrete::class)]
               public fixture\NestedDumpableConcrete $nested;
               
               
               public function __construct(array $unhintedScalarsDump)
                  {
                     $this->nested = new fixture\NestedDumpableConcrete(
                        fixture\UnhintedScalarsDumpable::restore($unhintedScalarsDump)
                     );
                  }
            });
            
            $arr = [
               'nested' => [
                  'nested' => $unhintedScalarsDump
               ]
            ];
            $this->assertSame($arr, $subj->dump());
            $this->assertSame($arr, $subj::restore($arr)->dump());
         }
         
      public function testHintedDumpableConcreteInexistentClass()
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropDumpable(class:'InexistentClass')]
               public ?dumpable\IDumpable $dumpable = null;
               
               public function __construct()
                  {
                     $this->dumpable = new fixture\DefaultValuesDumpable();
                  }
            });
            
            $this->expectException(\InvalidArgumentException::class);
            $subj->dump();
         }
         
      public function testHintedDumpableConcreteInvalidClass()
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropDumpable(class:\SplMaxHeap::class)]
               public ?dumpable\IDumpable $dumpable = null;
               
               public function __construct()
                  {
                     $this->dumpable = new fixture\DefaultValuesDumpable();
                  }
            });
            
            $this->expectException(\DomainException::class);
            $subj->dump();
         }
      
      
      /**
       * @depends testUnhintedScalarDump
       */
      public function testHintedDumpableAbstractDump(array $unhintedScalarsDump): array
         {
            $nested = fixture\UnhintedScalarsDumpable::restore($unhintedScalarsDump);
            $subj = new fixture\NestedDumpableAbstract($nested);
            
            $dump = [
               'nested' => [
                  '__class__' => $nested::class,
                  'state'     => $unhintedScalarsDump
               ]
            ];
            $this->assertSame($dump, $subj->dump());
            $this->assertSame([
               'nested' => $nested->dump(dumpable\IDumpable::DF_MODE_PUBLIC)
            ], $subj->dump(dumpable\IDumpable::DF_MODE_PUBLIC));
            
            return $dump;
         }
         
      /**
       * @depends testHintedDumpableAbstractDump
       * @depends testUnhintedScalarDump
       */
      public function testHintedDumpableAbstractRestore(array $dump, array $nestedDump): void
         {
            $subj = fixture\NestedDumpableAbstract::restore($dump);
            $this->assertInstanceOf(fixture\UnhintedScalarsDumpable::class, $subj->nested);
            $this->assertSame($nestedDump, $subj->nested->dump());
         }
         
      public function testHintedDumpableAbstractStateless(): void
         {
            $subj = new fixture\NestedDumpableAbstract(
               new fixture\StatelessDumpable()
            );
            
            $dump = [
               'nested' => [
                  '__class__' => fixture\StatelessDumpable::class
                  //empty state omitted in dump
               ]
            ];
            $this->assertSame($dump, $subj->dump());
            $this->assertSame([
               'nested' => []
            ], $subj->dump(dumpable\IDumpable::DF_MODE_PUBLIC));
            
            $rSubj = fixture\NestedDumpableAbstract::restore($dump);
            $this->assertInstanceOf(fixture\StatelessDumpable::class, $rSubj->nested);
         }
      
      public function testHintedDumpableAbstractRestoreMissingClass(): void
         {
            $dump = [
               'nested' => [
                  //missing __class__
                  'state' => []
               ]
            ];
            
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('missing required target class FQN');
            fixture\NestedDumpableAbstract::restore($dump);
         }
         
      public function testHintedDumpableAbstractRestoreInexistentClass(): void
         {
            $dump = [
               'nested' => [
                  '__class__' => '\NoSuchClass',
                  'state' => []
               ]
            ];
            
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid target class');
            fixture\NestedDumpableAbstract::restore($dump);
         }
      
      public function testHintedDumpableAbstractRestoreInvalidClass(): void
         {
            $dump = [
               'nested' => [
                  '__class__' => \SplMaxHeap::class,
                  'state' => []
               ]
            ];
            
            $this->expectException(\DomainException::class);
            $this->expectExceptionMessage('must implement IDumpable');
            fixture\NestedDumpableAbstract::restore($dump);
         }
      
      
      public function testHintedEnum()
         {
            $subj = (new class() implements dumpable\IDumpable {
               use dumpable\TDumpable;
               
               #[dumpable\attributes\PropEnum(class:fixture\IntEnum::class)]
               public fixture\IntEnum     $intEnum;
               
               #[dumpable\attributes\PropEnum(class:fixture\StringEnum::class)]
               public fixture\StringEnum  $strEnum;
               
               public function __construct()
                  {
                     //TODO: php 8.1 native enums
                     $this->intEnum = fixture\IntEnum::from(fixture\IntEnum::BAR);
                     $this->strEnum = fixture\StringEnum::from(fixture\StringEnum::FOO);
                  }
            });
            
            $arr = [
               'intEnum' => $subj->intEnum->value,
               'strEnum' => $subj->strEnum->value
            ];
            $this->assertSame($arr, $subj->dump());
            
            $rSubj = $subj::restore($arr);
            $this->assertSame($subj->intEnum->value, $rSubj->intEnum->value);
            $this->assertSame($subj->strEnum->value, $rSubj->strEnum->value);
         }
      
      /*public function testHintedEnumNotAnEnum()
         {
            //TODO: php 8.1
         }*/
   }
