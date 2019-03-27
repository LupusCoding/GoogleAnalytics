<?php

require_once ('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');

/**
 * Class ilGoogleAnalyticsPlugin
 * @author Ralph Dittrich <dittrich.ralph@lupuscoding.de>
 */
class ilGoogleAnalyticsPlugin extends ilUserInterfaceHookPlugin
{
	const PLUGIN_ID = "ganalytics";
	const PLUGIN_NAME = "GoogleAnalytics";
	const PLUGIN_SETTINGS = "qu_uihk_ganalytics";
	const PLUGIN_NS = 'LC\ILP\GoogleAnalytics';

	/** @var ilNewsManPlugin */
	protected static $instance;

	/** @var \ilSetting */
	protected $settings;

	/**
	 * @return void
	 */
	protected function init()
	{
		self::registerAutoloader();
	}

	/**
	 * @return ilNewsManPlugin
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return void
	 */
	public static function registerAutoloader()
	{
		global $DIC;

		if(!isset($DIC['autoload.lc.lcautoloader'])) {
			require_once(realpath(dirname(__FILE__)) . '/Autoload/LCAutoloader.php');
			$Autoloader = new LCAutoloader();
			$Autoloader->register();
			$Autoloader->addNamespace('ILIAS\Plugin', '/Customizing/global/plugins');
			$DIC['autoload.lc.lcautoloader'] = $Autoloader;
		}
		$DIC['autoload.lc.lcautoloader']->addNamespace(self::PLUGIN_NS, realpath(dirname(__FILE__)));
	}

	/**
	 * ilNewsManPlugin constructor.
	 */
	public function __construct() {
		parent::__construct();

		global $DIC;

		$this->db = $DIC->database();
		$this->settings = new ilSetting(self::PLUGIN_SETTINGS);
	}

	/**
	 * @return string
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}

	/**
	 * @return \ilSetting
	 */
	public function getSettings(): ilSetting
	{
		return $this->settings;
	}


	protected function afterActivation() {
		// Do something
	}

	protected function afterDeactivation() {
		// Do something
	}

	protected function beforeUninstall() {
		// Do something
		return true;
	}
}