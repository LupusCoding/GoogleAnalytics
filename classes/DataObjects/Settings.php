<?php

namespace LC\ILP\GoogleAnalytics\DataObjects;

class Settings
{
	const PL_GA_SETTING = 'pl_ga_set';

	private $settings;

	/** @var bool */
	private $active;

	/** @var string */
	private $analytics_token;

	/** @var string */
	private $tag_manager_token;

	/** @var bool */
	private $track_uid;

	/** @var string */
	private $uid_key;

	/** @var string */
	private $confirm_message;

	/** @var int */
	private $cookie_lifetime;

	public function __construct()
	{
		global $DIC;

		$this->settings = $DIC->settings();
		$this->load();
	}

	/**
	 * @return bool
	 */
	public function getActive(): bool
	{
		return (isset($this->active) ? $this->active : false);
	}

	/**
	 * @param bool $active
	 * @return Settings
	 */
	public function setActive(bool $active): Settings
	{
		$this->active = $active;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAnalyticsToken(): string
	{
		return (isset($this->analytics_token) ? $this->analytics_token : '');
	}

	/**
	 * @param string $analytics_token
	 * @return Settings
	 */
	public function setAnalyticsToken(string $analytics_token): Settings
	{
		$this->analytics_token = $analytics_token;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getTrackUid(): bool
	{
		return (isset($this->track_uid) ? $this->track_uid : false);
	}

	/**
	 * @param bool $track_uid
	 * @return Settings
	 */
	public function setTrackUid(bool $track_uid): Settings
	{
		$this->track_uid = $track_uid;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUidKey(): string
	{
		return (isset($this->uid_key) ? $this->uid_key : '');
	}

	/**
	 * @param string $uid_key
	 * @return Settings
	 */
	public function setUidKey(string $uid_key): Settings
	{
		$this->uid_key = $uid_key;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getConfirmMessage(): string
	{
		return (isset($this->confirm_message) ? $this->confirm_message : '');
	}

	/**
	 * @param string $confirm_message
	 * @return Settings
	 */
	public function setConfirmMessage(string $confirm_message): Settings
	{
		$this->confirm_message = $confirm_message;
		return $this;
	}

	/**
	 * @return \ilSetting
	 */
	public function getSettings(): \ilSetting
	{
		return $this->settings;
	}

	/**
	 * @param \ilSetting $settings
	 * @return Settings
	 */
	public function setSettings(\ilSetting $settings): Settings
	{
		$this->settings = $settings;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCookieLifetime(): int
	{
		return (isset($this->cookie_lifetime) ? $this->cookie_lifetime : 30);
	}

	/**
	 * @param int $cookie_lifetime
	 * @return Settings
	 */
	public function setCookieLifetime(int $cookie_lifetime): Settings
	{
		$this->cookie_lifetime = $cookie_lifetime;
		return $this;
	}



	public function load()
	{
		$set = json_decode($this->settings->get(self::PL_GA_SETTING, ''), true);
		$this->setActive(isset($set['active']) ? $set['active'] : false);
		$this->setAnalyticsToken(isset($set['token']) ? $set['token'] : '');
		$this->setTrackUid(isset($set['track_uid']) ? $set['track_uid'] : false);
		$this->setUidKey(isset($set['uid_key']) ? $set['uid_key'] : '');
		$this->setConfirmMessage(isset($set['confirm']) ? $set['confirm'] : '');
		$this->setCookieLifetime(isset($set['lifetime']) ? $set['lifetime'] : 30);
	}

	public function save()
	{
		$this->settings->set(self::PL_GA_SETTING, json_encode(
			[
				'active'    => $this->getActive(),
				'token'     => $this->getAnalyticsToken(),
				'track_uid' => $this->getTrackUid(),
				'uid_key'   => $this->getUidKey(),
				'confirm'   => $this->getConfirmMessage(),
				'lifetime'  => $this->getCookieLifetime(),
			]
		));
	}

}