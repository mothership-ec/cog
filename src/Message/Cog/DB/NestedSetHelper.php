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
			'left'  => $targetNode[$this->_right] + 1,
			'right' => $targetNode[$this->_right] + 2,
			'depth' => $targetNode[$this->_depth],
			'id'    => $nodeID,
		));

		return $this->_trans;
	}

	public function moveToTop($nodeID, $currentTop)
	{
		$node = $this->_getNode($nodeID);
		$currentTop = $this->_getNode($currentTop);

		$difference = 2;
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = (`' . $this->_left . '` - :difference?i),
				`' . $this->_right . '`  = (`' . $this->_right . '` - :difference?i)
			WHERE
				`' . $this->_right . '` <= :curright?i
			AND
				`' . $this->_left . '`  >= :nodeleft?i
			', array(
				'difference' => $difference,
				'curright'	=>  $currentTop[$this->_right],
				'nodeleft'	=> $node[$this->_left],
			)
		);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = ?i,
				`' . $this->_right . '`  = ?i
			WHERE
				`' . $this->_pk . '` = ?i
			', array(
				$currentTop[$this->_left],
				$currentTop[$this->_right],
				$nodeID,
			)
		);

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
	public function moveLeft($nodeID, $nextToID)
	{
		$this->_checkTableInfoSet();

		$node   = $this->_getNode($nodeID);
		$parent = $this->_getNode($nextToID);

		// We can't move any further left if this node's left edge is directly
		// after its parent's left edge.
		if ($node[$this->_left] === $parent[$this->_left] - 1 || $node[$this->_left] == 1) {
			return false;
		}


		if ($parent) {
			$this->_trans->add('
				SET @MOVE_AMOUNT_RIGHT = ?i
			', array(
				abs($parent[$this->_right] - $node[$this->_right]),
			));

			$this->_trans->add('
				SET @MOVE_AMOUNT_LEFT = ?i
			', 	array(
					abs($parent[$this->_left] - $node[$this->_left]),
				)
			);
		} else {
			// Get the difference of the right node from the current node we are moving
			$this->_trans->add('
				SET @MOVE_AMOUNT_RIGHT = (
					SELECT
						ABS(`' . $this->_right . '` - ?i )
					FROM
						`' . $this->_table . '`
					WHERE
						`' . $this->_right . '` =   ?i
					AND
						`' . $this->_depth . '` = ?i
				)
			', array(
				$node[$this->_right],
				$node[$this->_left] - 1,
				$node[$this->_depth],
			));

			// Work out the difference of the left position as above
			$this->_trans->add('
				SET @MOVE_AMOUNT_LEFT = (
					SELECT
						ABS(`' . $this->_left . '` - ?i )
					FROM
						page
					WHERE
						`' . $this->_right . '` =   ?i
					AND
						position_depth = ?i
				)
			', array(
					$node[$this->_left],
					$node[$this->_left] - 1,
					$node[$this->_depth],
				)
			);
		}

		// Get the children node ids of our node
		$childrenPageIDs = $this->_getNodeChildrenIDs($node);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` + @MOVE_AMOUNT_RIGHT,
				`' . $this->_right . '` = `' . $this->_right . '` + @MOVE_AMOUNT_RIGHT
			WHERE
				`' . $this->_left . '` <= ?i
			AND
				`' . $this->_left . '` >= (?i - @MOVE_AMOUNT_LEFT)
		', array(
				$node[$this->_left] - 1,
				$node[$this->_left],
			)
		);

		// Move the target element
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = `' . $this->_left . '` - @MOVE_AMOUNT_LEFT,
				`' . $this->_right . '` = `' . $this->_right . '` - @MOVE_AMOUNT_LEFT
			WHERE
				`' . $this->_pk . '` IN (?ij)
			', array(
				(array) $childrenPageIDs,
			)
		);

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

		// Get the difference of the right node from the current node we are moving
		$this->_trans->add('
			SET @MOVE_AMOUNT_RIGHT = (
				SELECT
					ABS(`' . $this->_right . '` - ?i )
				FROM
					page
				WHERE
					`' . $this->_left . '` =   ?i
				AND
					position_depth = ?i
			)
		', array(
			$node[$this->_right],
			$node[$this->_right] + 1,
			$node[$this->_depth],
		));

		// Work out the difference of the left position as above
		$this->_trans->add('
			SET @MOVE_AMOUNT_LEFT = (
				SELECT
					ABS(`' . $this->_left . '` - ?i )
				FROM
					page
				WHERE
					`' . $this->_left . '` =   ?i
				AND
					position_depth = ?i
			)
		', array(
				$node[$this->_left],
				$node[$this->_right] + 1,
				$node[$this->_depth],
			)
		);

		// Get the children node ids of our node
		$childrenPageIDs = $this->_getNodeChildrenIDs($node);

		// Chnage the position of all the nodes which are children of the next node
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_right . '`  = `' . $this->_right . '` -  @MOVE_AMOUNT_LEFT,
				`' . $this->_left . '` = `' . $this->_left . '` - @MOVE_AMOUNT_LEFT
			WHERE
				`' . $this->_right . '` <= (?i + @MOVE_AMOUNT_RIGHT)
			AND `' . $this->_left . '` > ?i
		', array(
				$node[$this->_right],
				$node[$this->_right],
			)
		);

		// Move the target element and it's children
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_right . '`  = `' . $this->_right . '` + @MOVE_AMOUNT_RIGHT,
				`' . $this->_left . '` = `' . $this->_left . '` + @MOVE_AMOUNT_RIGHT
			WHERE
				`' . $this->_pk . '` IN (?ij)
			', array(
				(array) $childrenPageIDs,
			)
		);

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

	public function moveNodeLeftOld($nodeID, $parentID)
	{
		var_dump('left');
		$node = $this->_getNode($nodeID, true);
		$parent = $this->_getNode($parentID, true);

		/**
		 * Adds a script to the transaction
		 */
		$this->_setNodeTotal($node);

		/**
		 * Returns an array of childrenIDs of the given node
		 */
		$childrenPageIDs = $this->_getNodeChildrenIDs($node);

		/**
		 *	$node left position - $parent right position
		 *	gives us the differece we need to change the children of $node
		 */
		$this->_trans->add('SET @NODE_MOVE_AMOUNT = ?i', array(
			abs($node[$this->_left] - $parent[$this->_right]),
		));

		/**
		 *	Getting the difference between the depths of nodeID #9 and nodeID #8
		 *	We would build this in the PHP I think
		 */
		if ($parent[$this->_depth] >= $node[$this->_depth]) {
			$depth = abs($node[$this->_depth] - $parent[$this->_depth]) + 1;
			$direction = 'down';
		} elseif ($parent[$this->_depth] + 1 == $node[$this->_depth]) {
			// The depth doesn't need to chnage here so a depth change of 0
			// will be added
			$depth = 0;
			$direction = 'flat';
		} else {
			$depth = $parent[$this->_depth] - 1;
			$direction = 'up';
		}

		$this->_trans->add('SET @NODE_DEPTH = ?i',
			array(
				$depth,
			)
		);

		/**
		 *	Increase all positions from nodeID #8's right hand side onwards,
		 *	except between nodeID #9's left and right
		 */
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_right . '` = (`' . $this->_right . '` + @NODEID_TOTAL)
			WHERE
				`' . $this->_right . '` >= ?i
			AND
				`' . $this->_right . '` < ?i
			AND
				`' . $this->_left . '` NOT BETWEEN ?i AND ?i',
			array(
				$parent[$this->_right],
				$node[$this->_right],
				$node[$this->_left],
				$node[$this->_right],
			)
		);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '` = (`' . $this->_left . '` + @NODEID_TOTAL)
			WHERE
				`' . $this->_left . '` >= ?i
			AND
				`' . $this->_left . '` <= ?i
			AND
				`' . $this->_left . '` NOT BETWEEN ?i AND ?i',
			array(
				$parent[$this->_right],
				$node[$this->_right],
				$node[$this->_left],
				$node[$this->_right],
			)
		);

		/**
		 *	Move the positions of left and right of children of nodeID #9 AND nodeID #9 itself
		 */
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = (`' . $this->_left . '`  - @NODE_MOVE_AMOUNT),
				`' . $this->_right . '`  = (`' . $this->_right . '` - @NODE_MOVE_AMOUNT),
				`' . $this->_depth . '`  = (`' . $this->_depth . '` + @NODE_DEPTH)
			WHERE
				`' . $this->_pk . '` IN (?ij)',
			array(
				(array) $childrenPageIDs,
			)
		);
		return $this->_trans;
	}

	public function moveNodeRightOld($nodeID, $parentID)
	{
		var_dump('right');
		$node = $this->_getNode($nodeID, true);
		$parent = $this->_getNode($parentID, true);
		/**
		 * Adds a script to the transaction
		 */
		$this->_setNodeTotal($node);

		/**
		 * Returns an array of childrenIDs of the given node
		 */
		$childrenPageIDs = $this->_getNodeChildrenIDs($node);

		/**
		 *	$node left position - $parent right position
		 *	gives us the differece we need to change the children of $node
		 */
		$this->_trans->add('SET @NODE_MOVE_AMOUNT = ?i', array(
			abs($node[$this->_left] - $parent[$this->_right]),
		));

		/**
		 *	Getting the difference between the depths of nodeID #9 and nodeID #8
		 *	We would build this in the PHP I think
		 */
		if ($parent[$this->_depth] >= $node[$this->_depth]) {
			$depth = abs($node[$this->_depth] - $parent[$this->_depth]) + 1;
			$direction = 'down';
		} elseif ($parent[$this->_depth] + 1 == $node[$this->_depth]) {
			// The depth doesn't need to chnage here so a depth change of 0
			// will be added
			$depth = 0;
			$direction = 'flat';
		} else {
			$depth = abs($parent[$this->_depth] - $node[$this->_depth]) - 1;
			$direction = 'up';
		}

		$this->_trans->add('SET @NODE_DEPTH = ?i',
			array(
				$depth,
			)
		);

		/**
		 *	Increase all positions from nodeID #8's right hand side onwards,
		 *	except between nodeID #9's left and right
		 */
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_right . '` = (`' . $this->_right . '` - @NODEID_TOTAL)
			WHERE
				`' . $this->_right . '` >= ?i
			AND
				`' . $this->_right . '` < ?i
			AND
				`' . $this->_left . '` NOT BETWEEN ?i AND ?i',
			array(
				$node[$this->_left],
				$parent[$this->_right],
				$node[$this->_left],
				$node[$this->_right],
			)
		);

		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '` = (`' . $this->_left . '` - @NODEID_TOTAL)
			WHERE
				`' . $this->_left . '` < ?i
			AND
				`' . $this->_left . '` > ?i
			AND
				`' . $this->_left . '` NOT BETWEEN ?i AND ?i
			AND `' . $this->_left . '` > 1',
			array(
				$parent[$this->_right],
				$node[$this->_left],
				$node[$this->_left],
				$node[$this->_right],
			)
		);

		/**
		 *	Move the positions of left and right of children of nodeID #9 AND nodeID #9 itself
		 */
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '`  = (`' . $this->_left . '`  + (@NODE_MOVE_AMOUNT - @NODEID_TOTAL)),
				`' . $this->_right . '`  = (`' . $this->_right . '` + (@NODE_MOVE_AMOUNT - @NODEID_TOTAL)),
				`' . $this->_depth . '`  = (`' . $this->_depth . '` - @NODE_DEPTH)
			WHERE
				`' . $this->_pk . '` IN (?ij)',
			array(
				(array) $childrenPageIDs,
			)
		);
		return $this->_trans;
	}

	public function _setNodeTotal($node)
	{
		$this->_trans->add('SET @NODEID_TOTAL = (
			SELECT
				/*
					count children and add itself multiplied
					by 2 to get the left and right difference
				*/
				(COUNT(`' . $this->_pk . '`) + 1 ) * 2
			FROM
				`' . $this->_table . '`
			WHERE
				`' . $this->_left . '` > ?i
			AND
				`' . $this->_right . '` < ?i
		)',array(
			$node[$this->_left],
			$node[$this->_right],
		));
	}

	protected function _getNodeChildrenIDs($node)
	{

		$result = $this->_query->run('
			SELECT
				`' . $this->_pk . '`
			FROM
				`' . $this->_table . '`
			WHERE
				`' . $this->_left . '` >= ?i
			AND
				`' . $this->_right . '` <= ?i
			', array(
				$node[$this->_left],
				$node[$this->_right],
		));

		/**
		 * Build an array of the children as we will need to update these using
		 * there ID's as things would have already moved and wtheir position may
		 * not be unique anymore
		 */
		$childrenPageIDs = array();
		foreach($result as $value) {
			$childrenPageIDs[] = $value->{$this->_pk};
		}

		return $childrenPageIDs;
	}

	public function _updateLeftPositions($direction, $start, $end, $plusMinus, array $children)
	{
		// Set the way the arrows need to point depending on the direction the
		// node is moving
		$direction = $direction == 'left' ? array('>','<=') : array('<','>=');

		# Add a tranasaction to the list where we update all left poitions
		# between the start and end numbers, either by adding or subtracting them
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_left . '` = `' . $this->_left . '` '.$plusMinus.' @LEFTRIGHT_DIFFERENCE
			WHERE
				`' . $this->_left . '` '.$direction[0].' ?i
			AND
				`' . $this->_left . '` '.$direction[1].' ?i

			'. ($children ? ' AND `'.$this->_pk.'` NOT IN (?ij) ' : ''). '
		', 	array(
				$start,
				$end,
				$children
			)
		);
	}

	public function _updateRightPositions($direction, $start, $end, $plusMinus, array $children)
	{
		// Set the way the arrows need to point depending on the direction the
		// node is moving
		$direction = $direction == 'left' ? array('>','<=') : array('<','>=');

		# Add a tranasaction to the list where we update all left poitions
		# between the start and end numbers, either by adding or subtracting them
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_right . '` = `' . $this->_right . '` '.$plusMinus.' @LEFTRIGHT_DIFFERENCE
			WHERE
				`' . $this->_right . '` '.$direction[0].' ?i
			AND
				`' . $this->_right . '` '.$direction[1].' ?i

			'. ($children ? ' AND `'.$this->_pk.'` NOT IN (?ij) ' : '' ). '
		', 	array(
				$start,
				$end,
				(array) $children
			)
		);
	}

	public function _updateChildrenNodes($plusMinus, array $children)
	{
		# Update the nodes between the given left and right positions
		$this->_trans->add('
			UPDATE
				`' . $this->_table . '`
			SET
				`' . $this->_right . '` = `' . $this->_right . '` '.$plusMinus.' @DIFFERENCE,
				`' . $this->_left . '` = `' . $this->_left . '` '.$plusMinus.' @DIFFERENCE,
				`' . $this->_depth . '` = `' . $this->_depth . '` + @NODE_DEPTH
			WHERE
				`'.$this->_pk.'` IN (?ij)
		', 	array(
				(array) $children
			)
		);
	}

	public function _calculateLeftRightChange($node)
	{
		$this->_trans->add('
			SET @LEFTRIGHT_DIFFERENCE = ?i
			', array(
				abs($node[$this->_left] - $node[$this->_right]) + 1,
			)
		);
	}

	public function _calculateDifferenceOfNodeAndChildren($direction, $node, $newPosition, $addAsChild)
	{
		$amount = $direction == 'right' ? $node[$this->_left] + ($addAsChild ? 1 : 0) : $node[$this->_right];
		$this->_trans->add('
			SET @DIFFERENCE = ABS(@LEFTRIGHT_DIFFERENCE - ?i)
			', array(
				abs($amount - $newPosition[$this->_right]),
			)
		);
	}

	public function _calculateDepth($node, $parent)
	{
		if ($parent[$this->_depth] == $node[$this->_depth]) {
			$depth = 0;
		} elseif ($parent[$this->_depth] < $node[$this->_depth]) {
			$depth = $parent[$this->_depth] + $node[$this->_depth];
		} else {
			$depth = $parent[$this->_depth] - $node[$this->_depth];
		}

		$this->_trans->add('SET @NODE_DEPTH = ?i',
			array(
				$depth,
			)
		);
	}

	public function moveNodeRight($nodeID, $newPosition, $addAsChild = false)
	{
		var_dump('right');
		$node = $this->_getNode($nodeID);
		$newPosition =  $this->_getNode($newPosition);

		// Sets transaction for the left/right position changes
		$this->_calculateLeftRightChange($node);
		// Set the transaction for the difference that we need to update the
		// left/right positions of the moving nodes
		$this->_calculateDifferenceOfNodeAndChildren('right', $node, $newPosition, $addAsChild);
		// Calculate the news depths
		$this->_calculateDepth($node, $newPosition);
		// Return an array of the ID's of the node and it's children
		$children = $this->_getNodeChildrenIDs($node);

		// Update the left positions
		$this->_updateLeftPositions(
			'right',
			$addAsChild ? $newPosition[$this->_left] : $newPosition[$this->_right],
			$node[$this->_left],
			'-',
			$children
		);
		// Update the right positions
		$this->_updateRightPositions(
			'right',
			$addAsChild ? $newPosition[$this->_left] : $newPosition[$this->_right],
			$node[$this->_right],
			'-',
			$children
		);
		// Update the node and it's children
		$this->_updateChildrenNodes('+', $children);
		// Return the transaction
		return $this->_trans;
	}

	public function moveNodeLeft($nodeID, $newPosition, $addAsChild = false)
	{
		var_dump('left');
		$node = $this->_getNode($nodeID);
		$newPosition =  $this->_getNode($newPosition);

		// Sets transaction for the left/right position changes
		$this->_calculateLeftRightChange($node);
		// Set the transaction for the difference that we need to update the
		// left/right positions of the moving nodes
		$this->_calculateDifferenceOfNodeAndChildren('left', $node, $newPosition, $addAsChild);
		// Calculate the news depths
		$this->_calculateDepth($node, $newPosition);
		// Return an array of the ID's of the node and it's children
		$children = $this->_getNodeChildrenIDs($node);
		// Update the left positions
		$this->_updateLeftPositions('left', $newPosition[$this->_right], $node[$this->_right], '+', $children);
		// Update the right positions
		$this->_updateRightPositions('left', $newPosition[$this->_right], $node[$this->_left],'+', $children);
		// Update the node and it's children
		$this->_updateChildrenNodes('-', $children);
		// Return the transaction
		return $this->_trans;
	}

}

