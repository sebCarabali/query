<?php

namespace Harp\Query;

use Harp\Query\Compiler;
use Harp\Query\SQL;

/**
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Delete extends AbstractWhere
{
    /**
     * @var SQL\Aliased[]|null
     */
    protected $table;

    /**
     * @var SQL\Aliased[]|null
     */
    protected $from;

    /**
     * @return SQL\Aliased[]|null
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param SQL\Aliased[] $table
     */
    public function setTable(array $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return Delete $this
     */
    public function clearTable()
    {
        $this->table = null;

        return $this;
    }

    /**
     * @return SQL\Aliased[]|null
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param SQL\Aliased[] $from
     */
    public function setFrom(array $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return Delete $this
     */
    public function clearFrom()
    {
        $this->from = null;

        return $this;
    }

    /**
     * @param  string|SQL\SQL $table
     * @return Delete                $this
     */
    public function table($table)
    {
        $this->table []= new SQL\Aliased($table);

        return $this;
    }

    /**
     * @param  string|SQL\SQL $table
     * @param  string         $alias
     * @return Delete                $this
     */
    public function from($table, $alias = null)
    {
        $this->from []= new SQL\Aliased($table, $alias);

        return $this;
    }

    /**
     * @return string
     */
    public function sql()
    {
        return Compiler\Delete::render($this);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return Compiler\Delete::parameters($this);
    }
}
