<?php

namespace LC\ILP\GoogleAnalytics\DataObjects;

/**
 * Class UserRelations
 * @package LC\ILP\GoogleAnalytics\DataObjects
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class UserRelations
{
	const DB_TABLE = 'ganalytics_urel';

	/** @var \ilDBInterface  */
	private $database;

	/** @var bool */
	private $update;
	/** @var int */
	private $user_id;
	/** @var string */
	private $ga_uid;
	/** @var bool */
	private $ga_track;
	/** @var string */
	private $updated_at;

	/**
	 * UserRelations constructor.
	 * @param null $id
	 */
	public function __construct($id = null)
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
	public function getUserId()
	{
		return isset($this->user_id) ? $this->user_id : 0;
	}

	/**
	 * @param int $user_id
	 * @return UserRelations
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getGaUid()
	{
		return isset($this->ga_uid) ? $this->ga_uid : 0;
	}

	/**
	 * @param string $ga_uid
	 * @return UserRelations
	 */
	public function setGaUid($ga_uid)
	{
		$this->ga_uid = $ga_uid;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getGaTrack()
	{
		return isset($this->ga_track) ? $this->ga_track : false;
	}

	/**
	 * @param bool $ga_track
	 * @return UserRelations
	 */
	public function setGaTrack($ga_track)
	{
		$this->ga_track = $ga_track;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getUpdatedAt(): int
	{
		return isset($this->updated_at) ? $this->updated_at : 0;
	}

	/**
	 * @param int $updated_at
	 */
	public function setUpdatedAt($updated_at = null)
	{
		$this->updated_at =  (isset($updated_at) ? $updated_at : time());
	}

	/**
	 * @return bool
	 */
	public function isTrackable()
	{
		return ($this->getGaTrack() || $this->getGaTrack() === null);
	}

	/**
	 * @return string
	 */
	private function hashUser()
	{
		return md5($this->getUserId());
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function loadById($id, $use_ga_uid = false)
	{
		if ($use_ga_uid === true) {
			$select = 'SELECT * FROM `' . self::DB_TABLE . '` WHERE ga_uid = ';
		} else {
			$select = 'SELECT * FROM `' . self::DB_TABLE . '` WHERE user_id = ';
		}
		$select .= $this->database->quote($id, 'integer');

		$result = $this->database->query($select);

		$res = $this->database->fetchAll($result);
		if (!empty($res)) {
			$res = $res[0];

			try {
				$this->setUserId($res['user_id']);
				$this->setGaUid($res['ga_uid']);
				$this->setGaTrack(($res['ga_track'] == 1));
				$this->setUpdatedAt(strtotime($res['updated_at']));
				$this->update = true;
				return true;
			} catch (\Exception $e) {
				return false;
			}
		} else {
			$this->setUserId($id);
			$this->setGaUid($this->hashUser());
			$this->setUpdatedAt();
			$this->save();
			$this->update = true;
			return true;
		}
	}

	/**
	 * @return void
	 */
	public function save()
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
	private function _create()
	{
		$types = [
			'integer',
			'integer',
			'integer',
			'timestamp'
		];
		$values = [
			$this->getUserId(),
			$this->getGaUid(),
			($this->getGaTrack() == true ? 1 : 0),
			($this->getUpdatedAt() > 0 ? date('Y-m-d H:i:s', $this->getUpdatedAt()) : null),
		];

		$query = 'INSERT INTO `' . self::DB_TABLE . '` ';
		$query .= '(`user_id`, `ga_uid`, `ga_track`, `updated_at`) ';
		$query .= 'VALUES (%s, %s, %s, %s) ';

		$this->database->manipulateF(
			$query,
			$types,
			$values
		);
	}

	/**
	 * @return void
	 */
	private function _update()
	{
		$types = [
			'integer',
			'integer',
			'timestamp',
			'integer',
		];
		$values = [
			$this->getGaUid(),
			($this->getGaTrack() == true ? 1 : 0),
			($this->getUpdatedAt() > 0 ? date('Y-m-d H:i:s', $this->getUpdatedAt()) : null),
			$this->getUserId()
		];

		$query = 'UPDATE `' . self::DB_TABLE . '` SET ';
		$query .= '`ga_uid` = %s, ';
		$query .= '`ga_track` = %s, ';
		$query .= '`updated_at` = %s, ';
		$query .= 'WHERE `user_id` = %s ';

		$this->database->manipulateF(
			$query,
			$types,
			$values
		);
	}
}