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
	 * @return ilGoogleAnalyticsAsyncGUI
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
	public static function getEntryLink(string $cmd = null): string
	{
		global $DIC;
		if (!isset($cmd)) {
			$cmd = self::$cmd_index;
		}

		$link = $DIC->ctrl()->getLinkTargetByClass([
			\ilUIPluginRouterGUI::class,
			self::class
		], $cmd, false, true, false);

		if (strpos($link, 'baseClass') !== false) {
			$link = preg_replace_callback('/baseClass=([^&]+)/i', function (array $matches) {
				return 'baseClass=' . \ilUIPluginRouterGUI::class;
			}, $link);
		} else {
			$link = ilUtil::appendUrlParameterString($link, 'baseClass=' . \ilUIPluginRouterGUI::class);
		}

		return $link;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();

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
		header('Content-Type: application/json');
		$ret = [
			'status' => 'failed'
		];
		if (isset($_POST) &&
			array_key_exists('ga_data', $_POST)
		) {
			$data = json_decode($_POST['ga_data'], true);
			if (
				array_key_exists('user_id', $data) &&
				array_key_exists('ga_choice', $data)
			) {

				$choice = ($data['ga_choice'] === 'true');
				$user_rel = new \LC\ILP\GoogleAnalytics\DataObjects\UserRelations();
				$user_rel->loadById($data['user_id'], true);
				$user_rel->setGaTrack($choice);
				$user_rel->setUpdatedAt();
				$user_rel->save();

				$ret['status'] = 'success';
			}
		}

		echo json_encode($ret);
		exit();
	}

}