<?php

namespace Message\Cog\DB;

/**
 * A helper class to make dealing with nested set database structures easier.
 *
 * Nested sets make representing heirarchical trees in relational databases
 * easier.
 *
 * @see    http://en.wikipedia.org/wiki/Nested_set_model
 *
 * @author James Moss <james@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class NestedSetHelper implements TransactionalInterface
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
	 * @param Query $query             The query instance to use
	 * @param Transaction $transaction The transaction instance to use
	 */
	public function __construct(Query $query, Transaction $transaction)
	{
		$this->_query = $query;
		$this->_trans = $transaction;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTransaction(Transaction $transaction)
	{
		$this->_trans = $transaction;
	}

	/**
	 * Sets information about the table to use for operations.
	 *
	 * @param string $table Table name to operate on
	 * @param string $pk    The name of the table's primary key column
	 * @param string $left  The table column to use for storing left edges
	 * @param string $right The table column to use for storing right edges
	 * @param string $depth The table column to use for storing depth level of nodes
	 *
	 * @return NestedSetHelper Returns $this for chainability
	 */
	public function setTable($table, $pk = null, $left = null, $right = null, $depth = null)
	{
		$this->_table = $table;
		$this->_pk = $pk ?: $table . '.' . $table . '_id';
		$this->_left = $left ?: $table . '.left';
		$this->_right = $right ?: $table . '.right';
		$this->_depth = $depth ?: $table . '.depth';

		return $this;
	}

	/**
	 * @param  Result $result      A database result to iterate over
	 * @param  string $childrenKey The array key to store child elements in
	 *
	 * @return false
	 *
	 * @deprecated  This method has never worked or done anything so you should not ever use it.
	 */
	public function toArray(Result $result, $childrenKey = 'children')
	{
		return false;
	}

	/**
	 * Insert a new node into the tree after a particular node.
	 *
	 * @see _checkTableInfoSet()
	 *
	 * @param  int $nodeID         Identifier for the new node to move after the
	 *                             sibling node
	 * @param  int $siblingNodeID  Identifier for the sibling node
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
				`' . $this->_right . '` = `' . $this->_right . '` + 2
			WHERE
				`' . $this->_right . '` > ?i
		', $targetNode[$this->_right]);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` + 2
			WHERE
				`' . $this->_left . '` > ?i
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
			'left' => $targetNode[$this->_right] + 1,
			'right' => $targetNode[$this->_right] + 2,
			'depth' => $targetNode[$this->_depth],
			'id' => $nodeID,
		));

		return $this->_trans;
	}


	/**
	 * Method to move a given node to another position in the tree
	 * Node will always be added *AFTER* the $newPosition unless $addBefore is true
	 *
	 * @param  int $nodeID                ID of the node to move
	 * @param  int $newPositionNodeID     ID of the node which is our reference
	 *                                    point of where we will move $nodeID to
	 * @param  boolean $changeParent      Toggle to recalculate the depth this
	 *                                    is used when adding new children and
	 *                                    moving the node into a new tree
	 * @param  boolean $addBefore         Toggle to add the $node before the $newPosition
	 *
	 * @return Transaction                The transaction to update all the nodes
	 */
	public function move($nodeID, $newPositionNodeID, $changeParent = false, $addBefore = false)
	{
		// Build the node information
		$node = $this->_getNode($nodeID);

		// Load all the nodes in the tree
		$tree = $this->_getAllNodesDepths();

		// Build the tree
		$tree = $this->_buildTree($tree);

		// Load the node for the new position
		$newPosition = $this->_getNode($newPositionNodeID, true);

		if (!isset($tree[$nodeID])) {
			throw new NestedSetException('Node with ID of `' . $nodeID . '`not found');
		}

		// Get the children and loop through and build an array (maintaining their order)
		// And then removing them from the original tree
		$children = $this->_getNodeChildrenIDs($node);
		$nodesToMove = array();

		foreach ($children as $childID) {
			if (!isset($tree[$childID])) {
				throw new NestedSetException('Child node ID of `' . $childID . '` not found');
			}

			// If changing the parent then we need to recalculate the depth for each
			// of the new children
			if ($changeParent) {
				$parentDepth = $newPosition[$this->_depth];
				if ($childID == $nodeID) {
					$tree[$childID][3] = $parentDepth + 1;
				} else {
					$change = ($tree[$childID][3] + 1) + ($parentDepth - $node[$this->_depth]);
					$tree[$childID][3] = $change;
				}
			}
			// Save the node
			$nodesToMove[$childID] = $tree[$childID];
			// Remove it from the tree
			unset($tree[$childID]);

		}

		// Even though we want to move the $node after the $newPosition we need to
		// actually move after all the children of $newPosition
		$exclude = array_keys($nodesToMove);

		$getNewChildren = $this->_getNodeChildrenIDs($newPosition, $exclude);

		// Get the last ID of the children, this is what we will add $node
		// (and it's children) after
		$idToAppend = array_pop($getNewChildren);

		// Build a new tree and insert the node and the children into the right
		// position of the tree. The tree keys get reset here, but thats ok
		// as we store it in the actual values too
		$newTree = array();

		foreach ($tree as $key => $values) {
			// If $node needs to be added at the top, then we do something a
			// little different and push the new tree after we have looked for
			// new position to add in our node and it's children
			// Otherwise add it after the new positions last child
			if ($addBefore) {
				if ($key == $newPosition['page_id']) {
					$newTree = array_merge($newTree, $nodesToMove);
				}
				array_push($newTree, $values);
			} else {
				array_push($newTree, $values);
				if ($key == $idToAppend) {
					$newTree = array_merge($newTree, $nodesToMove);
				}
			}
		}

		// We now need to build the array into the right way to recalculate the tree
		// before we save it to the DB
		$rebuild = array();

		foreach ($newTree as $values) {
			// Set the id as the key and the depth as the value
			$rebuild[$values[0]] = $values[3];
		}

		// Build the tree based on the new positions, ready for saving to the DB
		$tree = $this->_buildTree($rebuild);

		// Return the Transaction
		return $this->_saveTree($tree);

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

		$node = $this->_getNode($nodeID);
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
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` - 2,
				`' . $this->_right . '` = `' . $this->_right . '` - 2
			WHERE
				`' . $this->_pk . '` = ?i
		', [$nodeID]);

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

		$node = $this->_getNode($nodeID);
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
		', [$nodeID]);

		return $this->_trans;
	}

	/**
	 * Adds a node as the very first child of a parent node.
	 *
	 * @see _insertChild()
	 *
	 * @param int $nodeID    The ID of the child node
	 * @param int $parentID  The ID of the parent node
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
	 * @param int $nodeID    The ID of the child node
	 * @param int $parentID  The ID of the parent node
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
				`' . $this->_right . '` = `' . $this->_right . '` - 2
			WHERE
				`' . $this->_right . '` > ?i
		', $node[$this->_right]);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '` = `' . $this->_left . '` - 2
			WHERE
				`' . $this->_left . '` > ?i
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
		', [$nodeID]);

		return $this->_trans;
	}

	/**
	 * Adds a child element to a parent node as a direct descendant, either as
	 * the very first or very last node.
	 *
	 * @param int $nodeID       The ID of the child node
	 * @param int $parentID     The ID of the parent node
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
			', array(
					$parent[$this->_left]
				)
			);

			$newLeft = (int)$parent[$this->_left] + 1;
			$newRight = (int)$parent[$this->_left] + 2;
		} else {
			$this->_trans->add('
				UPDATE
					`' . $this->_table . '`
				SET
					`' . $this->_left . '` = `' . $this->_left . '` + 2
				WHERE
					`' . $this->_left . '` > ?i
			', array(
					$parent[$this->_right]
				)
			);

			$this->_trans->add('
				UPDATE
					`' . $this->_table . '`
				SET
					`' . $this->_right . '` = `' . $this->_right . '` + 2
				WHERE
					`' . $this->_right . '` >= ?i
			', array(
					$parent[$this->_right]
				)
			);

			$newLeft = (int)$parent[$this->_right];
			$newRight = (int)$parent[$this->_right] + 1;
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
			'id' => $nodeID,
			'left' => $newLeft,
			'right' => $newRight,
			'depth' => $parent[$this->_depth] + 1,
		));

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
			|| empty($this->_right) || empty($this->_depth)
		) {
			throw new \LogicException('Table data must be set before nested set operations can be performed.');
		}
	}

	/**
	 * Gets the left, right and depth values for a specific node ID
	 *
	 * @param  int $nodeID     The ID of the node to fetch
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
					`' . $this->_pk . '` = ?s
			', [$nodeID]);
		}

		if (!($node = $result->first()) && !$force) {
			throw new \InvalidArgumentException(sprintf('Node `%s:%s` does not exist.', $this->_pk, $nodeID));
		}

		return (array)$node;
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
				'left' => $node[$this->_left],
				'right' => $node[$this->_right],
				'depth' => $node[$this->_depth] - 1,
			));
		}

		return (array) $result->first();
	}

	/**
	 * Save the given tree to the database
	 *
	 * @param  array $data the updated tree ready to save
	 *
	 * @return Transaction    the transaction ready to run
	 */
	protected function _saveTree(array $data)
	{
		foreach ($data as $key => $values) {

			$expectedCount = 4;
			for ($i = 0; $i < $expectedCount; ++$i) {
				if (!array_key_exists($i, $values)) {
					throw new NestedSetException('Cannot save tree. Key `' . $i . '` missing from values when saving tree on row `' . $key .'` of data');
				}
			}

			$this->_trans->add('
				UPDATE
					`' . $this->_table . '`
				SET
					`' . $this->_left . '` = ?i ,
					`' . $this->_right . '` = ?i,
					`' . $this->_depth . '` = ?i
				WHERE
					`' . $this->_pk . '` = ?i',
				array(
					$values[1], // left position
					$values[2], // right position
					$values[3], // depth
					$values[0], // primary key
				)
			);
		}

		return $this->_trans;
	}

	/**
	 * Return an array of the nodeID and it's depth
	 *
	 * @return array    array of nodeID as key and depth as value
	 */
	protected function _getAllNodesDepths()
	{
		$result = $this->_query->run('
			SELECT
				`' . $this->_pk . '`,
				`' . $this->_depth . '`
			FROM
				`' . $this->_table . '`
			ORDER BY
				`' . $this->_left . '` ASC
		');
		$return = array();
		foreach ($result as $values) {
			$return[$values->{$this->_pk}] = $values->{$this->_depth};
		}
		return $return;
	}

	/**
	 * Method to calculate the nested set values by the the order given and
	 * the depth of the node
	 *
	 * @todo  Comment this method better
	 * @link  http://www.dotvoid.com/2007/09/reordering-nested-sets-using-php-and-javascript/
	 *
	 * @param  array $data array of node and depth
	 *
	 * @return array        the built tree with the left, right and depth
	 *                        position
	 */
	protected function _buildTree(array $data)
	{
		$lines = $data;
		$rows = array();
		$stack = array();

		$lft = 0;   // Left value
		$rgt = 0;   // Right value
		$plvl = -1; // Previous node level

		foreach ($lines as $id => $lvl) {

			// Skip empty/faulty lines
			if (trim($id) == '') {
				continue;
			}

			if ($lvl > $plvl) {
				$lft++;
				$rgt = 0;
				array_push($stack, $id);
			} elseif ($lvl == $plvl) {
				$pid = array_pop($stack);
				$rows[$pid][2] = $rows[$pid][1] + 1;
				$lft = $lft + 2;
				$rgt = 0;
				array_push($stack, $id);
			} else {
				$lft = $lft + ($plvl - $lvl) + 2;

				$diff = $plvl - $lvl + 1;
				for ($n = 0; $n < $diff; $n++) {
					$pid = array_pop($stack);
					if (null === $pid) {
						throw new NestedSetException('Nested set helper attempted to pull position ID from stack when stack is empty');
					}
					$rows[$pid][2] = $lft - $diff + $n;
				}
				array_push($stack, $id);
			}

			$rows[$id] = array($id, $lft, $rgt, $lvl);
			$plvl = $lvl;
		}

		$plvl++;
		$cnt = count($rows) * 2;
		$leftovers = count($stack);

		for ($n = 0; $n < $leftovers; $n++) {
			$pid = array_pop($stack);
			$plvl--;
			$rows[$pid][2] = $cnt - $plvl + $n;
			$cnt--;
		}

		return $rows;
	}

	protected function _getNodeChildrenIDs($node, $exclude = array())
	{
		if (!$exclude) {
			$exclude = '';
		}

		$result = $this->_query->run('
			SELECT
				`' . $this->_pk . '`
			FROM
				`' . $this->_table . '`
			WHERE
				`' . $this->_left . '` >= ?i
			AND
				`' . $this->_right . '` <= ?i
			AND
				`' . $this->_pk . '` NOT IN (?ij)
			ORDER BY
				`' . $this->_left . '` ASC',
			array(
				$node[$this->_left],
				$node[$this->_right],
				(array) $exclude,
			));

		/**
		 * Build an array of the children as we will need to update these using
		 * there ID's as things would have already moved and wtheir position may
		 * not be unique anymore
		 */
		$childrenPageIDs = array();
		foreach ($result as $value) {
			$childrenPageIDs[] = $value->{$this->_pk};
		}

		return $childrenPageIDs;
	}
}

