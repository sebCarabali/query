<?php
namespace Openbuildings\Cherry;

class Query_Update extends Query_Where {

	protected static $children_names = array(
		'TABLE',
		'SET',
		'WHERE',
		'LIMIT',
	);

	protected $current_having;

	public function __construct($tables = NULL)
	{
		parent::__construct('UPDATE');

		$tables = func_get_args();
		if ($tables) 
		{
			call_user_func(array($this, 'table'), $tables);
		}
	}

	public function table($tables)
	{
		$tables = func_get_args();

		$table_statements = array_map('Openbuildings\Cherry\Query::new_aliased_table', $tables);

		$this->set_list('TABLE', $table_statements, NULL, FALSE);

		return $this;
	}

	/**
	 * Choose the columns to select from.
	 *
	 * @param   mixed  $columns  column name or array($column, $alias) or object
	 * @return  $this
	 */
	public function set(array $pairs)
	{
		$sets = array();

		foreach ($pairs as $column => $value) 
		{
			$sets []= Query::new_set($column, $value);
		}

		$this->set_list('SET', $sets);

		return $this;
	}

	public function value($column, $value)
	{
		$this->set_list('SET', Query::new_set($column, $value));		

		return $this;
	}
}