<?php

namespace PHPCR\Tests\NodeTypeManagement\CND;

// TODO: fix coding style

require_once(__DIR__ . '/../../inc/BaseCase.php');

use PHPCR\Util\CND\Helper\NodeTypeGenerator,
    PHPCR\Util\CND\Reader\BufferReader,
    PHPCR\Util\CND\Parser\CndParser,
    PHPCR\Util\CND\Scanner\GenericScanner,
    PHPCR\Util\CND\Scanner\Context,
    PHPCR\PropertyType,
    PHPCR\Version\OnParentVersionAction;

/**
 * Test for PHPCR\Util\QOM\QomToSql2QueryConverter
 */
class CndParserTest extends \PHPCR\Test\BaseCase
{
    function testGenerator()
    {
        $cnd = <<<EOT
/*  An example node type definition */
<ns ='http://namespace.com/ns'>
[ns:NodeType] > ns:ParentType1, ns:ParentType2
  orderable mixin
  - ex:property (STRING)
  = 'default1' , 'default2'
    mandatory autocreated protected multiple
    VERSION
    < 'constraint1', 'constraint2'
  + ns:node (ns:reqType1, ns:reqType2)
    = ns:defaultType
    mandatory autocreated protected VERSION
EOT;

        $reader = new BufferReader($cnd);
        $scanner = new GenericScanner(new Context\DefaultScannerContextWithoutSpacesAndComments());
        $queue = $scanner->scan($reader);

        //define('DEBUG', true);

        $parser = new CndParser($queue);

        $generator = new NodeTypeGenerator(
            $this->sharedFixture['session']->getWorkspace(),
            $parser->parse()
        );

        $res = $generator->generate();
        $def = reset($res['nodeTypes']);

        $this->assertEquals(array('ns' => 'http://namespace.com/ns'), $res['namespaces']);

        $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeTemplateInterface', $def);
        $this->assertEquals('ns:NodeType', $def->getName());
        $this->assertEquals(array('ns:ParentType1', 'ns:ParentType2'), $def->getDeclaredSuperTypeNames());
        $this->assertTrue($def->hasOrderableChildNodes());
        $this->assertTrue($def->isMixin());
        $this->assertFalse($def->isQueryable());
        $this->assertFalse($def->isAbstract());
        $this->assertEquals(1, count($def->getPropertyDefinitionTemplates()));

        $prop = $def->getPropertyDefinitionTemplates()->getIterator()->current();

        $this->assertEquals('ex:property', $prop->getName());
        $this->assertEquals(PropertyType::STRING, $prop->getRequiredType());
        $this->assertEquals(array('default1', 'default2'), $prop->getDefaultValues());
        $this->assertEquals(array('constraint1', 'constraint2'), $prop->getValueConstraints());
        $this->assertTrue($prop->isAutoCreated());
        $this->assertTrue($prop->isMandatory());
        $this->assertTrue($prop->isProtected());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals(OnParentVersionAction::VERSION, $prop->getOnParentVersion());
        $this->assertEquals(array(), $prop->getAvailableQueryOperators());
        $this->assertTrue($prop->isFullTextSearchable()); // True because there was no "nofulltext" attribute
        $this->assertTrue($prop->isQueryOrderable());     // True because there was no "noqueryorder" attribute
    }

}
