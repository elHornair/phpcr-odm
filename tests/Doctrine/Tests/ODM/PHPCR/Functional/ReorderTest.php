<?php

namespace Doctrine\Tests\ODM\PHPCR\Functional;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Doctrine\ODM\PHPCR\Document\Generic;
use PHPCR\NodeInterface;

/**
 * @group functional
 */
class ReorderTest extends \Doctrine\Tests\ODM\PHPCR\PHPCRFunctionalTestCase
{
    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    private $dm;


    /**
     * @var NodeInterface
     */
    private $node;

    /**
     * @var array
     */
    private $childrenNames;

    public function setUp()
    {
        $this->dm = $this->createDocumentManager(array(__DIR__));
        $this->node = $this->resetFunctionalNode($this->dm);
        $parent = $this->dm->find(null, $this->node->getPath());
        $this->childrenNames = array('first', 'second', 'third', 'fourth');
        foreach ($this->childrenNames as $childName) {
            $child = new Generic();
            $child->setNodename($childName);
            $child->setParent($parent);
            $this->dm->persist($child);
        }
        $this->dm->flush();
    }

    public function testReorder() {
        $parent = $this->dm->find(null, $this->node->getPath());
        $children = $parent->getChildren();
        $this->assertSame($this->childrenNames, $this->getChildrenNames($children));

        $this->dm->reorder($parent, 'first', 'second', false);
        $this->dm->flush();
        $this->dm->clear();

        $parent = $this->dm->find(null, $this->node->getPath());
        $this->assertSame(array('second', 'first', 'third', 'fourth'), $this->getChildrenNames($parent->getChildren()));
    }


    public function testReorderNoObject() {
        $this->setExpectedException('InvalidArgumentException');
        $this->dm->reorder('parent', 'first', 'second', false);
        $this->dm->flush();
    }


    public function testReorderBeforeFirst() {
        $parent = $this->dm->find(null, $this->node->getPath());
        $children = $parent->getChildren();
        $this->assertSame($this->childrenNames, $this->getChildrenNames($children));

        $this->dm->reorder($parent, 'second', 'first', true);
        $this->dm->flush();
        $this->dm->clear();

        $parent = $this->dm->find(null, $this->node->getPath());
        $this->assertSame(array('second', 'first', 'third', 'fourth'), $this->getChildrenNames($parent->getChildren()));
    }

    public function testReorderAfterLast() {
        $parent = $this->dm->find(null, $this->node->getPath());
        $children = $parent->getChildren();
        $this->assertSame($this->childrenNames, $this->getChildrenNames($children));

        $this->dm->reorder($parent, 'first', 'fourth', false);
        $this->dm->flush();
        $this->dm->clear();

        $parent = $this->dm->find(null, $this->node->getPath());
        $this->assertSame(array('second', 'third', 'fourth', 'first'), $this->getChildrenNames($parent->getChildren()));
    }

    public function testReorderUpdatesChildren() {
        $parent = $this->dm->find(null, $this->node->getPath());
        $children = $parent->getChildren();
        $this->assertSame($this->childrenNames, $this->getChildrenNames($children));

        $this->dm->reorder($parent, 'first', 'second', false);
        $this->dm->flush();
        $this->assertSame(array('second', 'first', 'third', 'fourth'), $this->getChildrenNames($parent->getChildren()));
    }

    private function getChildrenNames($children)
    {
        $result = array();
        foreach ($children as $name => $child) {
            $result[] = $name;
        }
        return $result;
    }


}
