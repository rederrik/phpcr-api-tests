<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query\Sql1;

use PHPCR\Query\QueryInterface;

/**
 * Run non-trivial queries to try out where, the join features and such.
 */
class QueryOperationsTest extends QueryBaseCase
{
    public function testQueryField()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
                "SELECT foo FROM nt:unstructured WHERE foo = 'bar'",
            QueryInterface::SQL
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getNodes() as $node) {
            $vals[] = $node->getPropertyValue('foo');
        }
        $this->assertEquals(array('bar'), $vals);

        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }
        $this->assertEquals(array('bar'), $vals);
    }

    public function testQueryFieldSomenull()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT foo
            FROM nt:unstructured
            WHERE jcr:path LIKE \'/tests_general_base/%\'
            ',
            QueryInterface::SQL
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getNodes() as $node) {
            $vals[] = ($node->hasProperty('foo') ? $node->getPropertyValue('foo') : null);
        }
        $this->assertContains('bar', $vals);
        $this->assertCount(10, $vals);

        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }
        $this->assertContains('bar', $vals);
        $this->assertCount(10, $vals);
    }

    public function testQueryOrder()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT zeronumber
             FROM nt:unstructured
             WHERE jcr:path LIKE \'/tests_general_base/%\'
             ORDER BY zeronumber',
            QueryInterface::SQL
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('zeronumber');
        }
        // rows that do not have that field are null. empty is before fields with values
        $this->assertEquals(array(null, null, null, null, null, null, null, null, null, 0), $vals);
    }
}
