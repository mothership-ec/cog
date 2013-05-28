<?php

namespace Message\Cog\DB;

/**
 * A helper class to make dealing with nested set database structures easier.
 *
 * Nested sets make representing heirarchical trees in relational databases
 * easier.
 *
 * @see http://en.wikipedia.org/wiki/Nested_set_model
 *
 * @author James Moss <james@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class NestedSetHelper
{
	protected $_query;
	protected $_trans;

	protected $_table;
	protected $_pk;
	protected $_left;
	protected $_right;
	protected $_depth;

	/**
	 * Constructor.
	 *
	 * @param Query       $query       The query instance to use
	 * @param Transaction $transaction The transaction instance to use
	 */
	public function __construct(Query $query, Transaction $transaction)
	{
		$this->_query = $query;
		$this->_trans = $transaction;
	}

	/**
	 * Sets information about the table to use for operations.
	 *
	 * @param string $table    Table name to operate on
	 * @param string $pk       The name of the table's primary key column
	 * @param string $left     The table column to use for storing left edges
	 * @param string $right    The table column to use for storing right edges
	 * @param string $depth    The table column to use for storing depth level of nodes
	 *
	 * @return NestedSetHelper Returns $this for chainability
	 */
	public function setTable($table, $pk = null, $left = null, $right = null, $depth = null)
	{
		$this->_table = $table;
		$this->_pk    = $pk    ?: $table . '.' . $table . '_id';
		$this->_left  = $left  ?: $table . '.left';
		$this->_right = $right ?: $table . '.right';
		$this->_depth = $depth ?: $table . '.depth';

		return $this;
	}

	/**
	 * Converts a flat database result into an array.
	 *
	 * @see http://stackoverflow.com/a/886931
	 *
	 * @param  DBquery $result 		A database result to iterate over
	 * @param  string  $childrenKey The array key to store child elements in
	 *
	 * @return array                The result in a multidimensional array
	 *
	 * @todo Make this work. It's disabled because it doesn't work very well.
	 *       The behaviour seems to change depending how the $result is ordered.
	 */
	public function toArray(Result $result, $childrenKey = 'children')
	{
		return false;

		// Trees mapped
		$trees = array();
		$l = 0;
		// Node Stack. Used to help building the hierarchy
		$stack = array();

		foreach ($result as $node) {
			$item = (array) $node;
			$item[$childrenKey] = array();

			// Number of stack items
			$l = count($stack);

			// Check if we're dealing with different levels
			while($l > 0 && $stack[$l - 1][$this->_depth] >= $item[$this->_depth]) {
				array_pop($stack);
				$l--;
			}

			// Stack is empty (we are inspecting the root)
			if ($l == 0) {
				// Assigning the root node
				$i = count($trees);
				$trees[$i] = $item;
				$stack[] = & $trees[$i];
			} else {
				// Add node to parent
				$i = count($stack[$l - 1][$childrenKey]);
				$stack[$l - 1][$childrenKey][$i] = $item;
				$stack[] = & $stack[$l - 1][$childrenKey][$i];
			}
		}

		return $trees;
	}

	/**
	 * Insert a new node into the tree after a particular node.
	 *
	 * @see _checkTableInfoSet()
	 *
	 * @param  int  $nodeID        Identifier for the new node to move after the
	 *                             sibling node
	 * @param  int  $siblingNodeID Identifier for the sibling node
	 *
	 * @return Transaction         The transaction instance with the queries added
	 */
	public function insertAfter($nodeID, $siblingNodeID)
	{
		$this->_checkTableInfoSet();

		$targetNode = $this->_getNode($siblingNodeID);

		// Make space for the new element
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` + 2,
				`' . $this->_right . '` = `' . $this->_right . '` + 2
			WHERE
				`' . $this->_right . '` > ?i
		', $targetNode[$this->_right]);

		// Add the node into the tree
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = :left?i,
				`' . $this->_right . '` = :right?i,
				`' . $this->_depth . '` = :depth?i
			WHERE
				`' . $this->_pk . '` = :id?i
		', array(
			'left'  => $targetNode[$this->_right] + 1,
			'right' => $targetNode[$this->_right] + 2,
			'depth' => $targetNode[$this->_depth],
			'id'    => $nodeID,
		));

		return $this->_trans;
	}

	/**
	 * Move a node left in the tree.
	 *
	 * @param  int $nodeID       The ID of the node to move
	 *
	 * @return Transaction|false The transaction instance with the queries added,
	 *                           or false if the node could not be moved left
	 */
	public function moveLeft($nodeID)
	{
		$this->_checkTableInfoSet();

		$node   = $this->_getNode($nodeID);
		$parent = $this->_getParentNode($node);

		// We can't move any further left if this node's left edge is directly
		// after its parent's left edge.
		if ($node[$this->_left] === $parent[$this->_left] - 1) {
			return false;
		}

		// Take the element directly to the left and move it right one place
		$this->_trans->add('
			SET @LEFT_NODE = (
				SELECT
					`' . $this->_left . '`
				FROM
					`' . $this->_table . '`
				WHERE
					`' . $this->_right . '` = ?i
			)
		', $node[$this->_left] - 1);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` + 2,
				`' . $this->_right . '` = `' . $this->_right . '` + 2
			WHERE
				`' . $this->_left . '` >= @LEFT_NODE
			AND `' . $this->_right . '` < ?i
		', $node[$this->_left]);

		// Move the target element
		$trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` - 2,
				`' . $this->_right . '` = `' . $this->_right . '` - 2
			WHERE
				`' . $this->_pk . '` = ?i
		', $nodeID);

		return $this->_trans;
	}

	/**
	 * Move a node right in the tree.
	 *
	 * @param  int $nodeID       The ID of the node to move
	 *
	 * @return Transaction|false The transaction instance with the queries added,
	 *                           or false if the node could not be moved right
	 */
	public function moveRight($nodeID)
	{
		$this->_checkTableInfoSet();

		$node   = $this->_getNode($nodeID);
		$parent = $this->_getParentNode($node);

		// We can't move any further right if this nodes right edge is directly
		// before its parents right edge
		if ($node[$this->_right] === $parent[$this->_right] - 1) {
			return false;
		}

		// Take the element directly to the left and move it right one place
		$this->_trans->add('
			SET @LEFT_NODE = (
				SELECT
					`' . $this->_left . '`
				FROM
					`' . $this->_table . '`
				WHERE
					`' . $this->_right . '` = ?i
			)
		', $node[$this->_left] - 1);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` + 2,
				`' . $this->_right . '` = `' . $this->_right . '` + 2
			WHERE
				`' . $this->_left . '` >= @LEFT_NODE
			AND `' . $this->_right . '` < ?i
		', $node[$this->_left]);

		// Move the target element
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` - 2,
				`' . $this->_right . '` = `' . $this->_right . '` - 2
			WHERE
				`' . $this->_pk . '` = ?i
		', $nodeID);

		return $this->_trans;
	}

	/**
	 * Adds a node as the very first child of a parent node.
	 *
	 * @see _insertChild()
	 *
	 * @param int  $nodeID   The ID of the child node
	 * @param int  $parentID The ID of the parent node
	 * @param bool $force    True to suppress exceptions if the parent node
	 *                       doesn't exist
	 *
	 * @return Transaction   The transaction instance with the queries added
	 */
	public function insertChildAtStart($nodeID, $parentID, $force = false)
	{
		return $this->_insertChild($nodeID, $parentID, true, $force);
	}

	/**
	 * Adds a node as the very last child of a parent node.
	 *
	 * @see _insertChild()
	 *
	 * @param int  $nodeID   The ID of the child node
	 * @param int  $parentID The ID of the parent node
	 * @param bool $force    True to suppress exceptions if the parent node
	 *                       doesn't exist
	 *
	 * @return Transaction   The transaction instance with the queries added
	 */
	public function insertChildAtEnd($nodeID, $parentID, $force = false)
	{
		return $this->_insertChild($nodeID, $parentID, false, $force);
	}

	/**
	 * Adds a child element to a parent node as a direct descendant, either as
	 * the very first or very last node.
	 *
	 * @param int 	  $nodeID   The ID of the child node
	 * @param int 	  $parentID The ID of the parent node
	 * @param boolean $atStart  When true the child is inserted as the first
	 *                          element, otherwise the last
	 * @param bool $force       True to suppress exceptions if the parent node
	 *                          doesn't exist
	 *
	 * @return Transaction      The transaction instance with the queries added
	 */
	protected function _insertChild($nodeID, $parentID, $atStart = true, $force)
	{
		$this->_checkTableInfoSet();

		$parent = $this->_getNode($parentID, $force);

		// Make space for the new element
		if (true === $atStart) {
			$this->_trans->add('
				UPDATE
					`' . $this->_table . '`
				SET
					`' . $this->_left . '`  = `' . $this->_left . '` + 2,
					`' . $this->_right . '` = `' . $this->_right . '` + 2
				WHERE
					`' . $this->_left . '` > ?i
			', $parent[$this->_left]);

			$newLeft  = (int) $parent[$this->_left] + 1;
			$newRight = (int) $parent[$this->_left] + 2;
		}
		else {
			$this->_trans->add('
				UPDATE
					`' . $this->_table . '`
				SET
					`' . $this->_left . '` = `' . $this->_left . '` + 2
				WHERE
					`' . $this->_left . '` > ?i
			', $parent[$this->_right]);

			$this->_trans->add('
				UPDATE
					`' . $this->_table . '`
				SET
					`' . $this->_right . '` = `' . $this->_right . '` + 2
				WHERE
					`' . $this->_right . '` >= ?i
			', $parent[$this->_right]);

			$newLeft  = (int) $parent[$this->_right];
			$newRight = (int) $parent[$this->_right] + 1;
		}

		// Insert the new element
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = :left?i,
				`' . $this->_right . '` = :right?i,
				`' . $this->_depth . '` = :depth?i
			WHERE
				`' . $this->_pk . '` = :id?i
		', array(
			'id'    => $nodeID,
			'left'  => $newLeft,
			'right' => $newRight,
			'depth' => $parent[$this->_depth] + 1,
		));

		return $this->_trans;
	}

	/**
	 * Removes a node from the tree without deleting it.
	 *
	 * @param  int $nodeID The ID of the node to remove
	 *
	 * @return Transaction The transaction instance with the queries added
	 */
	public function remove($nodeID)
	{
		$this->_checkTableInfoSet();

		$node = $this->_getNode($nodeID);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` - 2,
				`' . $this->_right . '` = `' . $this->_right . '` - 2
			WHERE
				`' . $this->_right . '` > ?i
		', $node[$this->_right]);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = NULL,
				`' . $this->_right . '` = NULL,
				`' . $this->_depth . '` = NULL
			WHERE
				`' . $this->_pk . '` = ?i
		', $nodeID);

		return $this->_trans;
	}

	/**
	 * Checks that all the required table information has been set in order to
	 * perform an operation such as moving or inserting a node.
	 *
	 * @throws \LogicException If any required table information has not been set
	 */
	protected function _checkTableInfoSet()
	{
		if (empty($this->_table) || empty($this->_pk) || empty($this->_left)
		 || empty($this->_right) || empty($this->_depth)) {
			throw new \LogicException('Table data must be set before nested set operations can be performed.');
		}
	}

	/**
	 * Gets the left, right and depth values for a specific node ID
	 *
	 * @param  int     $nodeID The ID of the node to fetch
	 * @param  boolean $force  True to suppress the exception thrown if the node
	 *                         does not exist
	 *
	 * @return array           An array with the left, right and depth values
	 *
	 * @throws \InvalidArgumentException If the node does not exist
	 */
	protected function _getNode($nodeID, $force = false)
	{
		if ($force && !$nodeID) {
			$result = $this->_query->run('
				SELECT
					-1  AS `' . $this->_pk . '`,
					0   AS `' . $this->_left . '`,
					MAX(`' . $this->_right . '`) + 1 AS `' . $this->_right . '`,
					-1  AS `' . $this->_depth . '`
				FROM
					`' . $this->_table . '`
			');
		}
		else {
			$result = $this->_query->run('
				SELECT
					`' . $this->_pk . '`,
					`' . $this->_left . '`,
					`' . $this->_right . '`,
					`' . $this->_depth . '`
				FROM
					`' . $this->_table . '`
				WHERE
					`' . $this->_pk . '` = ?s
			', $nodeID);
		}

		if (!($node = $result->first()) && !$force) {
			throw new \InvalidArgumentException(sprintf('Node `%s:%s` does not exist.', $this->_pk, $nodeID));
		}

		return (array) $node;
	}

	/**
	 * Gets the left, right and depth values for the parent of a specific node.
	 *
	 * @param  array $node An array of left, right and depth values for the node
	 *                     to find the parent for (from `_getNode()`)
	 *
	 * @return array       An array with the left, right and depth values
	 */
	protected function _getParentNode($node)
	{
		// If we're at the top level get a faux parent node so we can still move
		// 0 depth nodes left and right
		if ($node[$this->_depth] === 0) {
			$result = $this->_query->run('
				SELECT
					-1  AS `' . $this->_pk . '`,
					0   AS `' . $this->_left . '`,
					MAX(`' . $this->_right . '`) + 1 AS `' . $this->_right . '`,
					-1  AS `' . $this->_depth . '`
				FROM
					`' . $this->_table . '`
			');
		} else {
			$result = $this->_query->run('
				SELECT
					`' . $this->_pk . '`,
					`' . $this->_left . '`,
					`' . $this->_right . '`,
					`' . $this->_depth . '`
				FROM
					`' . $this->_table . '`
				WHERE
					`' . $this->_left . '` < :left?i
				AND `' . $this->_right . '` > :right?i
				AND `' . $this->_depth . '` = :depth?i
			', array(
				'left'  => $node[$this->_left],
				'right' => $node[$this->_right],
				'depth' => $node[$this->_depth] - 1,
			));
		}

		return (array) $result->first();
	}
}

