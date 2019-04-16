<?php

namespace LC\ILP\GoogleAnalytics\Views;

/**
 * Class AbstractView
 * @package LC\ILP\GoogleAnalytics\Views
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
abstract class AbstractView
{
	/** @var \ilGoogleAnalyticsPlugin */
	protected $plugin;

	/** @var \ilTemplate */
	protected $tpl;

	/** @var \ilCtrl */
	protected $ctrl;

	/** @var \ilDBInterface */
	protected $database;

	/** @var \ilTabsGUI */
	protected $tabs;

	/** @var \ilLanguage */
	protected $lng;

	/** @var \ilToolbarGUI */
	protected $toolbar;

	/** @var string */
	protected static $cmd_index;

	/**
	 * Tab [
	 * 	 'id' => string
	 * 	 'txt' => string
	 *   'classes' => array
	 *   'cmd' => string
	 * ]
	 *
	 * @return array
	 */
	abstract public function getTabs(): array;

	/**
	 * @return string
	 */
	abstract public function getActiveTab(): string;

	/**
	 * @return string
	 */
	abstract public function getTitle(): string;

	/**
	 * @return string
	 */
	abstract public function getId(): string;

	/**
	 * @return self
	 */
	abstract public static function getInstance();

	/**
	 * Example:
	 * {
	 *   global $DIC;
	 *   return $DIC->ctrl()->getLinkTargetByClass([
	 *     \ilMyClassGUI::class,
	 *     get_class()
	 *   ], $cmd, '', $async);
	 * }
	 *
	 * @param string|null $cmd
	 * @param bool $async
	 * @return string
	 */
	abstract public static function getEntryLink(string $cmd = null, bool $async = false): string;

	/**
	 * handle commands
	 *
	 * @return void
	 */
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

		$this->tpl->getStandardTemplate();
		$this->setTitle();
		$this->setTabs();
		$this->tpl->show();
	}

	/**
	 * constructor.
	 */
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
	 * @return void
	 */
	public final function setTitle()
	{
		$this->tpl->setTitle($this->getTitle());
	}

	/**
	 * @return void
	 */
	public final function setTabs()
	{
		if (!empty($this->getTabs())) {
			foreach ($this->getTabs() as $tab) {
				$this->tabs->addTab($tab['id'], $tab['txt'], $this->ctrl->getLinkTargetByClass($tab['classes'], $tab['cmd']));
			}
		}
		$activetab = $this->getActiveTab();
		if (isset($activetab)) {
			$this->tabs->activateTab($this->getActiveTab());
		}
	}

	/**
	 * @return array
	 */
	public final function getProtoTab(): array
	{
		return [
			'id' => '',
			'txt' => '',
			'classes' => [],
			'cmd' => '',
		];
	}

	/**
	 * Get translated value
	 *
	 * @param string $a_var
	 * @return string
	 */
	public final function txt(string $a_var): string
	{
		return $this->plugin->txt($a_var);
	}

}