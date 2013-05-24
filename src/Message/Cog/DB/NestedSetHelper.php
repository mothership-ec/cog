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
	protected $_transaction;

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
		$this->_query       = $query;
		$this->_transaction = $transaction;
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
		$this->_depth = $right ?: $table . '.depth';

		return $this;
	}

	/**
	 * Converts a flat database result into an array.
	 *
	 * Adapted from http://stackoverflow.com/a/886931
	 *
	 * @param  DBquery $result 		A database result to iterate over
	 * @param  string  $childrenKey The array key to store child elements in
	 *
	 * @return array   The result in a multidimensional array
	 *
	 * @todo  Dont hardcode the depth key.
	 * @todo  use new DB result
	 */
	public function toArray(Result $result, $childrenKey = 'children')
	{
		// Trees mapped
		$trees = array();
		$l = 0;
		// Node Stack. Used to help building the hierarchy
		$stack = array();

		while($node = $result->row()) {
			$item = $node;
			$item[$childrenKey] = array();

			// Number of stack items
			$l = count($stack);

			// Check if we're dealing with different levels
			while($l > 0 && $stack[$l - 1]['depth'] >= $item['depth']) {
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
	 * @return Transaction         The transaction object with the queries added
	 */
	public function insertAfter($nodeID, $siblingNodeID)
	{
		$this->_checkTableInfoSet();

		$targetNode = $this->_getNode($siblingNodeID, $force);

		// Make space for the new element
		$this->_trans->add('
			UPDATE
				:table?s
			SET
				:left?s  = :left?s + 2,
				:right?s = :right?s + 2
			WHERE
				:right?s > :targetRight?i
		', array(
			'table'       => $this->_table,
			'left'        => $this->_left,
			'right'       => $this->_right,
			'targetRight' => (int) $targetNode[$this->_right]
		));

		// Add the node into the tree
		$this->_trans->add('
			UPDATE
				:table?s
			SET
				:left?s  = :leftVal?i,
				:right?s = :rightVal?i,
				:depth?s = :depthVal?i
			WHERE
				:pk?s = :id?i
		', array(
			'table'    => $this->_table,
			'left'     => $this->_left,
			'right'    => $this->_right,
			'depth'    => $this->_depth,
			'pk'       => $this->_pk,
			'leftVal'  => (int) $targetNode[$this->_right] + 1,
			'rightVal' => (int) $targetNode[$this->_right] + 2,
			'depthVal' => (int) $targetNode[$this->_depth],
			'id'       => (int) $nodeID,
		));

		return $this->_trans;
	}

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
					:left?s
				FROM
					:table?s
				WHERE
					:right?s = :value?i
			)
		', array(
			'table' => $this->_table,
			'left'  => $this->_left,
			'right' => $this->_right,
			'value' => $node[$this->_left] - 1,
		));

		$this->_trans->add('
			UPDATE
				:table?s
			SET
				:left?s  = :left?s + 2,
				:right?s = :right?s + 2
			WHERE
				:left?s >= @LEFT_NODE
				AND :right?s < :currentLeft?i
		', array(
			'table'       => $this->_table,
			'left'        => $this->_left,
			'right'       => $this->_right,
			'currentLeft' => $node[$this->_left],
		));

		// Move the target element
		$trans->add('
			UPDATE
				:table?s
			SET
				:left?s  = :left?s - 2,
				:right?s = :right?s - 2
			WHERE
				:pk?s = :id?i
		', array(
			'table' => $this->_table,
			'left'  => $this->_left,
			'right' => $this->_right,
			'pk'    => $this->_pk,
			'id'    => (int) $nodeID,
		));

		return $this->_trans;
	}

	public function moveRight($nodeID)
	{
		$this->_checkTableInfoSet();

		$node   = $this->_getNode($nodeID);
		$parent = $this->_getParentNode($node);

		// Check if we can't move any further right if this nodes right edge is directly before its parents right edge.
		if($node[$this->_right] === $parent[$this->_right] - 1) {
			return false;
		}

		// Take the element directly to the left and move it right one place
		$trans = new DBtransaction;
		$trans->add("
			SET @LEFT_NODE = (
				SELECT
					" . $this->_left . "
				FROM
					" . $this->_table . "
				WHERE
					" . $this->_right . " = " . ($node[$this->_left] - 1) . "
			)
		");
		$trans->add("
			UPDATE
				" . $this->_table . "
			SET
				" . $this->_left . "  = " . $this->_left . " + 2,
				" . $this->_right . " = " . $this->_right . " + 2
			WHERE
				" . $this->_left . " >= @LEFT_NODE
				AND " . $this->_right . " < " . $node[$this->_left] . "
		");

		// Move the target element
		$trans->add("
			UPDATE
				" . $this->_table . "
			SET
				" . $this->_left . "  = " . $this->_left . " - 2,
				" . $this->_right . " = " . $this->_right . " - 2
			WHERE
				" . $this->_pk . " = " . (int)$nodeID . "
		");

		return $trans->run();
	}

	/**
	 * Adds a node as the very first child of a parent node
	 *
	 * @param int $nodeID   The ID of the child node
	 * @param int $parentID The ID of the parent node
	 */
	public function insertChildAtStart($nodeID, $parentID, $force = false)
	{
		return $this->_insertChild($nodeID, $parentID, true, $force);
	}

	/**
	 * Adds a node as the very first child of a parent node
	 *
	 * @param int $nodeID   The ID of the child node
	 * @param int $parentID The ID of the parent node
	 */
	public function insertChildAtEnd($nodeID, $parentID, $force = false)
	{
		return $this->_insertChild($nodeID, $parentID, false, $force);
	}

	/**
	 * Adds a child element to a parent node as a direct descendant, either as the very first element or the very last.
	 *
	 * @param int 	  $nodeID   The ID of the child node
	 * @param int 	  $parentID The ID of the parent node
	 * @param boolean $atStart  When true child is inserted as the first element, otherwise the last.
	 */
	protected function _insertChild($nodeID, $parentID, $atStart = true, $force)
	{
		$this->_checkTableInfoSet();

		$parent = $this->_getNode($parentID, $force);

		// Make space for the new element
		$trans = new DBtransaction;


		if($atStart === true) {
			$trans->add("
				UPDATE
					" . $this->_table . "
				SET
					" . $this->_left . "  = " . $this->_left . " + 2,
					" . $this->_right . " = " . $this->_right . " + 2
				WHERE
					" . $this->_left . " > " . (int)$parent[$this->_left] . "
			");
			$newLeft  = (int)$parent[$this->_left]+1;
			$newRight = (int)$parent[$this->_left]+2;
		} else {
			$trans->add("
				UPDATE
					" . $this->_table . "
				SET
					" . $this->_left . " = " . $this->_left . " + 2
				WHERE
					" . $this->_left . " > " . (int)$parent[$this->_right] . "
			");
			$trans->add("
				UPDATE
					" . $this->_table . "
				SET
					" . $this->_right . " = " . $this->_right . " + 2
				WHERE
					" . $this->_right . " >= " . (int)$parent[$this->_right] . "
			");
			$newLeft  = (int)$parent[$this->_right];
			$newRight = (int)$parent[$this->_right]+1;
		}


		// insert the new element
		$trans->add("
			UPDATE
				" . $this->_table . "
			SET
				" . $this->_left . "  = " . $newLeft . ",
				" . $this->_right . " = " . $newRight . ",
				" . $this->_depth . " = " . ($parent[$this->_depth]+1) . "
			WHERE
				" . $this->_pk . " = " . (int)$nodeID . "
		");

		return $trans->run();
	}

	/**
	 * Removes a node from the tree but doesnt delete it.
	 *
	 * @param  int 		$nodeID The ID of the node to remove.
	 * @return boolean True if the node was removed successfully.
	 */
	public function remove($nodeID)
	{
		$node = $this->_getNode($nodeID);

		$trans->add("
			UPDATE
				" . $this->_table . "
			SET
				" . $this->_left . "  = " . $this->_left . " - 2,
				" . $this->_right . " = " . $this->_right . " - 2
			WHERE
				" . $this->_right . " > " . (int)$node[$this->_right] . "
		");

		$trans->add("
			UPDATE
				" . $this->_table . "
			SET
				" . $this->_left . "  = NULL,
				" . $this->_right . " = NULL
			WHERE
				" . $this->_pk . " = " . (int)$nodeID . "
		");

		return $trans->run();
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
	 * @param  boolean $force  If the node doesnt exist an exception is thrown. Setting to true surpresses this.
	 * @return array   An array where the left, right and depth values are keys in the array.
	 */
	protected function _getNode($nodeID, $force = false)
	{
		if($force && !$nodeID) {
			$db = new DBquery("
				SELECT
					-1  AS `" . $this->_pk . "`,
					0   AS `" . $this->_left . "`,
					MAX(" . $this->_right . ") + 1 AS `" . $this->_right . "`,
					-1  AS `" . $this->_depth . "`
				FROM
					". $this->_table ."
			");
		} else {
			$db = new DBquery("
				SELECT
					" . $this->_pk . "     AS `" . $this->_pk . "`,
					" . $this->_left . "   AS `" . $this->_left . "`,
					" . $this->_right . "  AS `" . $this->_right . "`,
					" . $this->_depth  . " AS `" . $this->_depth . "`
				FROM
					" . $this->_table . "
				WHERE
					" . $this->_pk . " = " . (int)$nodeID . "
			");
		}


		if(!($node = $db->row()) && !$force) {
			throw new \Exception(sprintf('Node `%s:%s` does not exist.', $this->_pk, $nodeID));
		}

		return $node;
	}

	protected function _getParentNode($node)
	{
		// If we're at the top level get a faux parent node so we can still move 0 depth nodes left and right
		if($node[$this->_depth] === 0) {
			$sql = "
				SELECT
					-1  AS `" . $this->_pk . "`,
					0   AS `" . $this->_left . "`,
					MAX(" . $this->_right . ") + 1 AS `" . $this->_right . "`,
					-1  AS `" . $this->_depth . "`
				FROM
					". $this->_table ."
			";
		} else {
			$sql = "
				SELECT
					" . $this->_pk . "     AS `" . $this->_pk . "`,
					" . $this->_left . "   AS `" . $this->_left . "`,
					" . $this->_right . "  AS `" . $this->_right . "`,
					" . $this->_depth  . " AS `" . $this->_depth . "`
				FROM
					" . $this->_table . "
				WHERE
					" . $this->_left . " < " . $node[$this->_left] . "
					AND " . $this->_right . " > " . $node[$this->_right] . "
					AND depth = " . ($node[$this->_depth] - 1) . "
			";
		}

		$db = new DBquery($sql);

		return $db->row();
	}
}

