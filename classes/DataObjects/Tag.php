<?php

namespace LC\ILP\GoogleAnalytics\DataObjects;

/**
 * Class Tag
 * @package LC\ILP\GoogleAnalytics\DataObjects
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class Tag
{
	const DB_TABLE = 'ganalytics_tags';

	/** @var \ilDBInterface */
	private $database;

	/** @var bool */
	private $update;

	/** @var int */
	private $id = 0;
	/** @var string */
	private $name = '';
	/** @var string */
	private $type = '';
	/** @var string */
	private $definition = '';

	/**
	 * Tag constructor.
	 * @param int $id
	 */
	public function __construct(int $id = null)
	{
		global $DIC;

		$this->database = $DIC->database();
		$this->update = false;
		if ($id !== null) {
			$this->loadById($id);
		}
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Tag
	 */
	public function setId(int $id): Tag
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return Tag
	 */
	public function setName(string $name): Tag
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return Tag
	 */
	public function setType(string $type): Tag
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefinition(): string
	{
		return $this->definition;
	}

	/**
	 * @param string $definition
	 * @return Tag
	 */
	public function setDefinition(string $definition): Tag
	{
		$this->definition = $definition;
		return $this;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function loadById(int $id): bool
	{
		$select = 'SELECT * FROM `' . self::DB_TABLE . '` WHERE id = ' .
			$this->database->quote($id, 'integer');

		$result = $this->database->query($select);
		$res = $this->database->fetchAll($result);

		if (empty($res)) {
			return false;
		}
		$res = $res[0];

		$this->setId((int)$res['id']);
		$this->setName($res['name']);
		$this->setType($res['type']);
		$this->setDefinition($res['definition']);

		$this->update = true;
		return true;
	}

	/**
	 * @return bool
	 */
	public function delete(): bool
	{
		$types = [
			'integer',
		];
		$values = [
			$this->getId(),
		];

		$query = 'DELETE FROM `' . self::DB_TABLE . '` ';
		$query .= 'WHERE `id` = %s ';

		$res = $this->database->manipulateF(
			$query,
			$types,
			$values
		);

		return ($res !== false);
	}

	/**
	 * @return void
	 */
	public function save(): void
	{

		if ($this->update == true) {
			// update existing
			$this->_update();

		} else {
			// create new
			$this->_create();
		}
	}

	/**
	 * @return void
	 */
	private function _create(): void
	{
		$types = [
			'integer',
			'text',
			'text',
			'text',
		];
		$this->setId($this->database->nextId(self::DB_TABLE));
		$values = [
			$this->getId(),
			$this->getName(),
			$this->getType(),
			$this->getDefinition(),
		];

		$query = 'INSERT INTO `' . self::DB_TABLE . '` ';
		$query .= '(id, name, type, definition) ';
		$query .= 'VALUES (%s, %s, %s, %s) ';

		$this->database->manipulateF(
			$query,
			$types,
			$values
		);
		$this->update = true;
	}

	/**
	 * @return void
	 */
	private function _update(): void
	{
		$types = [
			'text',
			'text',
			'text',
			'integer',
		];
		$values = [
			$this->getName(),
			$this->getType(),
			$this->getDefinition(),
			$this->getId(),
		];

		$query = 'UPDATE `' . self::DB_TABLE . '` SET ';
		$query .= 'name = %s, ';
		$query .= 'type = %s, ';
		$query .= 'definition = %s ';
		$query .= 'WHERE `id` = %s ';

		$this->database->manipulateF(
			$query,
			$types,
			$values
		);
	}

}