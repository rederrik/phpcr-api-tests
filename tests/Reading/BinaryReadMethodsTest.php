<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Reading;

use PHPCR\PropertyInterface;

// According to PHPCR\BinaryInterface

/**
 * §5.10.5.
 */
class BinaryReadMethodsTest extends \PHPCR\Test\BaseCase
{
    /** @var PropertyInterface */
    private $binaryProperty;
    private $decodedstring = 'h1. Chapter 1 Title

* foo
* bar
** foo2
** foo3
* foo0

|| header || bar ||
| h | j |

{code}
hello world
{code}

# foo
';

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->session->getRootNode()->getNode('tests_general_base/numberPropertyNode/jcr:content');
        $this->binaryProperty = $this->node->getProperty('jcr:data');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $this->binaryProperty->getType());
    }

    public function testReadBinaryValue()
    {
        $binary = $this->binaryProperty->getBinary();
        $this->assertTrue(is_resource($binary));
        $this->assertEquals($this->decodedstring, stream_get_contents($binary));

        // stream must start when getting again
        $binary = $this->binaryProperty->getBinary();
        $this->assertTrue(is_resource($binary));
        $this->assertEquals($this->decodedstring, stream_get_contents($binary), 'Stream must begin at start again on second read');

        // stream must not be the same
        fclose($binary);
        $binary = $this->binaryProperty->getBinary();
        $this->assertTrue(is_resource($binary));
        $this->assertEquals($this->decodedstring, stream_get_contents($binary), 'Stream must be different for each call, fclose should not matter');
    }

    public function testIterateBinaryValue()
    {
        foreach ($this->binaryProperty as $value) {
            $this->assertEquals($this->decodedstring, stream_get_contents($value));
        }
    }

    public function testReadBinaryValueAsString()
    {
        $s = $this->binaryProperty->getString();
        $this->assertInternalType('string', $s);
        $this->assertEquals($this->decodedstring, $s);
    }

    public function testGetLength()
    {
        $size = $this->binaryProperty->getLength();
        $this->assertInternalType('integer', $size);
        $this->assertEquals(strlen($this->decodedstring), $size);
    }

    public function testReadBinaryValues()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/index.txt/jcr:content');
        $binaryMulti = $node->getProperty('multidata');
        $this->assertTrue($binaryMulti->isMultiple());
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $binaryMulti->getType());
        $vals = $binaryMulti->getValue();
        $this->assertInternalType('array', $vals);
        foreach ($vals as $value) {
            $this->assertTrue(is_resource($value));
            $this->assertEquals($this->decodedstring, stream_get_contents($value));
        }
    }

    public function testReadBinaryValuesAsString()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/index.txt/jcr:content');
        $binaryMulti = $node->getProperty('multidata');
        $this->assertTrue($binaryMulti->isMultiple());
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $binaryMulti->getType());
        $vals = $binaryMulti->getString();
        $this->assertInternalType('array', $vals);
        foreach ($vals as $value) {
            $this->assertInternalType('string', $value);
            $this->assertEquals($this->decodedstring, $value);
        }
    }

    public function testGetLengthMultivalue()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/index.txt/jcr:content');
        $binaryMulti = $node->getProperty('multidata');
        $sizes = $binaryMulti->getLength();
        $this->assertInternalType('array', $sizes);
        foreach ($sizes as $size) {
            $this->assertInternalType('integer', $size);
            $this->assertEquals(strlen($this->decodedstring), $size);
        }
    }

    public function testReadBinaryPathEncoding()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/index.txt/jcr:content');
        $binary = $node->getProperty('encoding?%$-test');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $binary->getType());
        $value = $binary->getString();
        $this->assertInternalType('string', $value);
        $this->assertEquals($this->decodedstring, $value);
    }

    public function testReadBinaryPathTrailingQuestionmark()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/index.txt/jcr:content');
        $binary = $node->getProperty('encoding?');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $binary->getType());
        $value = $binary->getString();
        $this->assertInternalType('string', $value);
        $this->assertEquals($this->decodedstring, $value);
    }
}
