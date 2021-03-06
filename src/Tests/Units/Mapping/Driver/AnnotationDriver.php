<?php

namespace Mapado\RestClientSdk\Tests\Units\Mapping\Driver;

use atoum;
use Mapado\RestClientSdk\Mapping\Relation;

/**
 * Class AnnotationDriver
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class AnnotationDriver extends atoum
{
    /**
     * testClassWithoutEntityAnnotation
     *
     * @access public
     * @return void
     */
    public function testClassWithoutEntityAnnotation()
    {
        $this
            ->given($this->newTestedInstance($this->getCacheDir(), true))
            ->then
                ->if($mapping = $this->testedInstance->loadClassname('Mapado\RestClientSdk\Tests\Model\Client'))
                ->array($mapping)
                    ->isEmpty()
        ;
    }

    /**
     * testAnnotationDriver
     *
     * @access public
     * @return void
     */
    public function testAnnotationDriver()
    {
        $this
            ->given($this->newTestedInstance($this->getCacheDir(), true))
            ->then
                ->if($mapping = $this->testedInstance->loadClassname('Mapado\RestClientSdk\Tests\Model\Product'))
                ->array($mapping)
                    ->size->isEqualTo(1)
                ->object($classMetadata = current($mapping))
                    ->isInstanceOf('Mapado\RestClientSdk\Mapping\ClassMetadata')
                ->string($classMetadata->getKey())
                    ->isEqualTo('product')

                ->string($classMetadata->getModelName())
                    ->isEqualTo('Mapado\RestClientSdk\Tests\Model\Product')

                ->string($classMetadata->getClientName())
                    ->isEqualTo('Mapado\Foo\Bar\Client')

                ->array($classMetadata->getAttributeList())
                    ->size->isEqualTo(3)

                ->object($attribute = current($classMetadata->getAttributeList()))
                    ->isInstanceOf('Mapado\RestClientSdk\Mapping\Attribute')

                ->string($attribute->getName())
                    ->isEqualTo('id')
        ;
    }

    public function testAnnotationDriverWithRelations()
    {
        $this
            ->given($this->newTestedInstance($this->getCacheDir(), true))
            ->then
                ->if($mapping = $this->testedInstance->loadClassname('Mapado\RestClientSdk\Tests\Model\Cart'))
                ->array($mapping)
                    ->size->isEqualTo(1)
                ->object($classMetadata = current($mapping))
                    ->isInstanceOf('Mapado\RestClientSdk\Mapping\ClassMetadata')
                ->string($classMetadata->getKey())
                    ->isEqualTo('cart')

                ->array($classMetadata->getAttributeList())
                    ->size->isEqualTo(4)

                ->array($classMetadata->getRelationList())
                    ->size->isEqualTo(1)

                ->string(current($classMetadata->getRelationList())->getType())
                    ->isEqualTo(Relation::ONE_TO_MANY)

            ->then
                ->if($mapping = $this->testedInstance->loadClassname('Mapado\RestClientSdk\Tests\Model\CartItem'))
                ->array($mapping)
                    ->size->isEqualTo(1)
                ->object($classMetadata = current($mapping))
                    ->isInstanceOf('Mapado\RestClientSdk\Mapping\ClassMetadata')
                ->string($classMetadata->getKey())
                    ->isEqualTo('cart_item')

                ->array($classMetadata->getAttributeList())
                    ->size->isEqualTo(5)

                ->array($classMetadata->getRelationList())
                    ->size->isEqualTo(1)

            ->then($relation = current($classMetadata->getRelationList()))
                ->string($relation->getType())
                    ->isEqualTo(Relation::MANY_TO_ONE)

                ->string($relation->getTargetEntity())
                    ->isEqualTo('Mapado\RestClientSdk\Tests\Model\Cart')
        ;
    }

    public function testLoadDirectory()
    {
        $this
            ->given($this->newTestedInstance($this->getCacheDir(), true))
            ->then
                ->if($mapping = $this->testedInstance->loadDirectory(__DIR__ . '/../../../Model'))
                ->array($mapping)
                    ->size->isEqualTo(3)
        ;
    }

    /**
     * getCacheDir
     *
     * @access private
     * @return string
     */
    private function getCacheDir()
    {
        return __DIR__ . '/../../../cache/';
    }
}
