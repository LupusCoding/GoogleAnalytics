<?php

namespace LC\ILP\GoogleAnalytics\DataObjects;

/**
 * Class TagCollection
 * @package LC\ILP\GoogleAnalytics\DataObjects
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class TagCollection implements \Countable
{
	/** @var \ilDBInterface  */
	protected $database;

	/** @var array */
	protected $tags;

	/**
	 * TagCollection constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->tags = [];
		$this->database = $DIC->database();
		$this->loadTags();
	}

	/**
	 * @return array|Tag[]
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->tags);
	}

	/**
	 * @return bool
	 */
	public function loadTags(): bool
	{
		$select = 'SELECT * FROM `' . Tag::DB_TABLE . '`;';

		$result = $this->database->query($select);
		$res = $this->database->fetchAll($result);

		if (empty($res)) {
			return false;
		}
		$this->tags = [];

		foreach ($res as $item) {
			$tag = new Tag();

			$tag->setId((int)$item['id']);
			$tag->setName($item['name']);
			$tag->setType($item['type']);
			$tag->setDefinition($item['definition']);

			$this->tags[] = $tag;
		}
		return true;
	}
}