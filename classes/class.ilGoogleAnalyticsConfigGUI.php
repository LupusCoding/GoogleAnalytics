<?php

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');

use \LC\ILP\GoogleAnalytics\DataObjects\Settings;

/**
 * Class ilGoogleAnalyticsConfigGUI
 * @author Ralph Dittrich <dittrich.ralph@lupuscoding.de>
 */
class ilGoogleAnalyticsConfigGUI extends ilPluginConfigGUI
{
	/** @var \ilGoogleAnalyticsPlugin */
	protected $plugin;

	/** @var \ilCtrl */
	protected $ctrl;

	/** @var \ilLanguage */
	protected $lng;

	/** @var \ilTemplate */
	protected $tpl;
	
	/** @var \LC\ILP\GoogleAnalytics\DataObjects\Settings */
	protected $settings;

	/**
	 * ilGoogleAnalyticsConfigGUI constructor.
	 */
	public function construct()
	{
		global $DIC;

		$this->plugin = ilGoogleAnalyticsPlugin::getInstance();
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];
		$this->settings = new Settings();
	}

	/**
	 * @param $cmd
	 * @return void
	 */
	public function performCommand($cmd)
	{
		$this->construct();
		$next_class = $this->ctrl->getNextClass($this);

		switch ($next_class) {
			default:
				switch ($cmd) {
					default:
						$this->{$cmd}();
						break;
				}
				break;
		}
	}

	/**
	 * @return void
	 */
	protected function configure()
	{
		$form = $this->getConfigurationForm();

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getConfigurationForm(): ilPropertyFormGUI
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->getPluginName() . ' ' . $this->txt("plugin_configuration"));

		$cb = new \ilCheckboxInputGUI($this->txt('active'), 'active');
		$cb->setChecked($this->settings->getActive());
		$form->addItem($cb);

		$ti = new \ilTextInputGUI($this->txt('ga_token'), 'token');
		$ti->setInfo($this->txt('ga_token_info'));
		$ti->setValue($this->settings->getAnalyticsToken());
		$form->addItem($ti);

		$cb = new \ilCheckboxInputGUI($this->txt('track_user'), 'track_uid');
		$cb->setChecked($this->settings->getTrackUid());

		$ti = new \ilTextInputGUI($this->txt('uid_key'), 'uid_key');
		$ti->setInfo($this->txt('uid_key_info'));
		$ti->setValue($this->settings->getUidKey());
		$cb->addSubItem($ti);

		$form->addItem($cb);

		$rgi = new \ilRadioGroupInputGUI($this->txt('opt_in_out'), 'opt_in_out');

		$rgo = new \ilRadioOption($this->txt('opt_in'), Settings::PL_GA_OPT_IN);
		$rgo->setInfo($this->txt('opt_in_info'));

		$ti = new \ilTextInputGUI($this->txt('confirm_message'), 'confirm');
		$ti->setInfo($this->txt('confirm_message_info'));
		$ti->setValue($this->settings->getConfirmMessage());
		$rgo->addSubItem($ti);

		$rgi->addOption($rgo);

		$rgo = new \ilRadioOption($this->txt('opt_out'), Settings::PL_GA_OPT_OUT);
		$rgo->setInfo($this->txt('opt_out_info'));

		$ne = new \ilNonEditableValueGUI($this->txt('opt_out_code'), 'opt_out_code');

		$ne->setInfo($this->txt('opt_out_code_info'));
		$snippet_tpl = '<a class="ga_trackbutton" href="#"></a>';
		$ne->setValue($snippet_tpl);
		$rgo->addSubItem($ne);

		$ti = new \ilTextInputGUI($this->txt('sentence_active'), 'sentence_active');
		$ti->setInfo($this->txt('sentence_active_info'));
		$ti->setValue($this->settings->getSentenceActive());
		$rgo->addSubItem($ti);

		$ti = new \ilTextInputGUI($this->txt('sentence_inactive'), 'sentence_inactive');
		$ti->setInfo($this->txt('sentence_inactive_info'));
		$ti->setValue($this->settings->getSentenceInactive());
		$rgo->addSubItem($ti);

		$rgi->addOption($rgo);

		$rgi->setValue($this->settings->getOptInOut());
		$form->addItem($rgi);

		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * @return void
	 */
	public function save()
	{
		$form = $this->getConfigurationForm();

		if ($form->checkInput()) {
			// save...
			if ($form->getInput('active')) {
				$this->settings->setActive(($form->getInput('active') == true));
			}
			if ($form->getInput('token')) {
				$this->settings->setAnalyticsToken($form->getInput('token'));
			}
			if ($form->getInput('track_uid')) {
				$this->settings->setTrackUid(($form->getInput('track_uid') == true));
			}
			if ($form->getInput('uid_key')) {
				$this->settings->setUidKey($form->getInput('uid_key'));
			}
			if ($form->getInput('opt_in_out')) {
				$this->settings->setOptInOut($form->getInput('opt_in_out'));
			}
			if ($form->getInput('confirm')) {
				$this->settings->setConfirmMessage($form->getInput('confirm'));
			}
			if ($form->getInput('sentence_active')) {
				$this->settings->setSentenceActive($form->getInput('sentence_active'));
			}
			if ($form->getInput('sentence_inactive')) {
				$this->settings->setSentenceInactive($form->getInput('sentence_inactive'));
			}

			$this->settings->save();

			ilUtil::sendSuccess($this->txt("saving_invoked"), true);
			$this->ctrl->redirect($this, "configure");

		} else {
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHtml());
		}
	}

	/**
	 * @param $a_var
	 * @return string
	 */
	protected function txt($a_var): string
	{
		return $this->plugin->txt($a_var);
	}

}