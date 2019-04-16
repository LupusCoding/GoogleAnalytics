<?php

/**
 * Class ilGoogleAnalyticsAsyncGUI
 * @author Ralph Dittrich <dittrich@qualitus.de>
 *
 * @ilCtrl_isCalledBy ilGoogleAnalyticsAsyncGUI:ilUIPluginRouterGUI
 */
class ilGoogleAnalyticsAsyncGUI
{
	const ID             = 'googleanalytics_async';
	const TITLE          = '';
	const CMD_INDEX      = 'index';

	/** @var ilGoogleAnalyticsAsyncGUI */
	protected static $instance;

	/** @var string  */
	protected static $cmd_index = self::CMD_INDEX;

	public final function __construct()
	{
		global $DIC;

		$this->plugin = \ilGoogleAnalyticsPlugin::getInstance();
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC->ctrl();
		$this->database = $DIC->database();
		$this->tabs = $DIC->tabs();
		$this->lng = $DIC->language();
		$this->toolbar = $DIC->toolbar();
	}

	/**
	 * @return array
	 */
	public function getTabs(): array
	{
		return [];
	}

	/**
	 * @return string
	 */
	public function getActiveTab(): string
	{
		return '';
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return \ilGoogleAnalyticsPlugin::getInstance()->txt(self::TITLE);
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return self::ID;
	}

	/**
	 * @return ilGoogleAnalyticsAsyncGUI|\LC\ILP\GoogleAnalytics\Views\AbstractView
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param string|null $cmd
	 * @param bool $async
	 * @return string
	 */
	public static function getEntryLink(string $cmd = null, bool $async = false): string
	{
		global $DIC;
		if (!isset($cmd)) {
			$cmd = self::$cmd_index;
		}
		return $DIC->ctrl()->getLinkTargetByClass([
			\ilUIPluginRouterGUI::class,
			self::class
		], $cmd, false, true, false);
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();

		global $DIC;
		$DIC->logger()->root()->debug($cmd);

		switch ($next_class) {
			default:
				switch ($cmd) {
					default:
						$this->$cmd();
						break;
				}
				break;
		}
	}

	/**
	 * @return void
	 */
	protected function index()
	{}

	/**
	 * @return void
	 */
	public function setflag()
	{
		global $DIC;
		$DIC->logger()->root()->debug('setflag is triggered!');

		header('Content-Type: application/json');
		$ret = [
			'status' => 'failed'
		];
		if (isset($_POST) &&
			array_key_exists('user_id', $_POST) &&
			array_key_exists('ga_choice', $_POST)) {

			$choice = ($_POST['ga_choice'] === 'true');

			$user_rel = new \LC\ILP\GoogleAnalytics\DataObjects\UserRelations();
			$user_rel->loadById((int) $_POST['user_id'], true);
			$user_rel->setGaTrack($choice);
			$user_rel->setUpdatedAt();
			$user_rel->save();

			$ret['status'] = 'success';
		}

		echo json_encode($ret);
		exit();
	}

}