<?php

namespace Harp\Query\Test\Compiler;

use Harp\Query\Test\AbstractTestCase;
use Harp\Query\Compiler;
use Harp\Query;
use Harp\Query\SQL\SQL;

/**
 * @group compiler
 * @group compiler.update
 * @coversDefaultClass Harp\Query\Compiler\Update
 *
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class UpdateTest extends AbstractTestCase
{

    /**
     * @covers ::render
     * @covers ::parameters
     */
    public function testUpdate()
    {

        $update = new Query\Update;
        $update
            ->type('IGNORE')
            ->table('table1')
            ->table('table2', 'alias1')
            ->order('col1', 'ASC')
            ->joinAliased('join1', 'alias_join1', array('col' => 'col2'))
            ->limit(10)
            ->where('test', 'value')
            ->whereRaw('test_statement = IF ("test", ?, ?)', array('val1', 'val2'))
            ->set(array('post' => 'new value', 'name' => new SQL('IF ("test", ?, ?)', array('val3', 'val4'))))
            ->setMultiple(array(
                1 => array(
                    'param1' => 'new value',
                    'param3' => 'multi val 1',
                    'param2' => 123
                ),
                2 => array(
                    'param1' => 'new value',
                    'param3' => 'multi val 2',
                )
            ))
            ->whereRaw('type > ? AND type < ? OR base IN ?', array(10, 20, array('1', '2', '3')));

        $expectedSql = <<<SQL
UPDATE IGNORE `table1`, `table2` AS `alias1` JOIN `join1` AS `alias_join1` ON `col` = `col2` SET `post` = ?, `name` = IF ("test", ?, ?), `param1` = CASE `id` WHEN ? THEN ? WHEN ? THEN ? ELSE `param1` END, `param3` = CASE `id` WHEN ? THEN ? WHEN ? THEN ? ELSE `param3` END, `param2` = CASE `id` WHEN ? THEN ? ELSE `param2` END WHERE (`test` = ?) AND (test_statement = IF ("test", ?, ?)) AND (type > ? AND type < ? OR base IN (?, ?, ?)) ORDER BY `col1` ASC LIMIT 10
SQL;
        $this->assertEquals($expectedSql, Compiler\Update::render($update));

        $expectedParameters = array(
            'new value',
            'val3',
            'val4',
            1,
            'new value',
            2,
            'new value',
            1,
            'multi val 1',
            2,
            'multi val 2',
            1,
            123,
            'value',
            'val1',
            'val2',
            10,
            20,
            '1',
            '2',
            '3',
        );


        $this->assertEquals($expectedParameters, Compiler\Update::parameters($update));
    }
}
