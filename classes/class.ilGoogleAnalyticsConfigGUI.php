<?php

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');

use \LC\ILP\GoogleAnalytics\DataObjects\Settings;

/**
 * Class ilGoogleAnalyticsConfigGUI
 * @author Ralph Dittrich <dittrich.ralph@lupuscoding.de>
 *
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
	protected function getConfigurationForm()
	{
		$settings = new Settings();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->getPluginName() . ' ' . $this->txt("plugin_configuration"));

		$cb = new \ilCheckboxInputGUI($this->txt('active'), 'active');
		$cb->setChecked($settings->getActive());
		$form->addItem($cb);

		$ti = new \ilTextInputGUI($this->txt('ga_token'), 'token');
		$ti->setInfo($this->txt('ga_token_info'));
		$ti->setValue($settings->getAnalyticsToken());
		$form->addItem($ti);

		$cb = new \ilCheckboxInputGUI($this->txt('track_user'), 'track_uid');
		$cb->setChecked($settings->getTrackUid());

		$ti = new \ilTextInputGUI($this->txt('uid_key'), 'uid_key');
		$ti->setInfo($this->txt('uid_key_info'));
		$ti->setValue($settings->getUidKey());
		$cb->addSubItem($ti);

		$form->addItem($cb);

		$rgi = new \ilRadioGroupInputGUI($this->txt('opt_in_out'), 'opt_in_out');

		$rgo = new \ilRadioOption($this->txt('opt_in'), Settings::PL_GA_OPT_IN);
		$rgo->setInfo($this->txt('opt_in_info'));

		$ti = new \ilTextInputGUI($this->txt('confirm_message'), 'confirm');
		$ti->setInfo($this->txt('confirm_message_info'));
		$ti->setValue($settings->getConfirmMessage());
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
		$ti->setValue($settings->getSentenceActive());
		$rgo->addSubItem($ti);

		$ti = new \ilTextInputGUI($this->txt('sentence_inactive'), 'sentence_inactive');
		$ti->setInfo($this->txt('sentence_inactive_info'));
		$ti->setValue($settings->getSentenceInactive());
		$rgo->addSubItem($ti);

		$rgi->addOption($rgo);

		$rgi->setValue($settings->getOptInOut());
		$form->addItem($rgi);

		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		return $form;
	}

	public function save()
	{
		$settings = new Settings();

		$form = $this->getConfigurationForm();

		if ($form->checkInput()) {
			// save...
			if ($_POST['active']) {
				$settings->setActive(($_POST['active'] == true));
			}
			if ($_POST['token']) {
				$settings->setAnalyticsToken($_POST['token']);
			}
			if ($_POST['track_uid']) {
				$settings->setTrackUid(($_POST['track_uid'] == true));
			}
			if ($_POST['uid_key']) {
				$settings->setUidKey($_POST['uid_key']);
			}
			if ($_POST['opt_in_out']) {
				$settings->setOptInOut($_POST['opt_in_out']);
			}
			if ($_POST['confirm']) {
				$settings->setConfirmMessage($_POST['confirm']);
			}
			if ($_POST['sentence_active']) {
				$settings->setSentenceActive($_POST['sentence_active']);
			}
			if ($_POST['sentence_inactive']) {
				$settings->setSentenceInactive($_POST['sentence_inactive']);
			}

			$settings->save();

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
	protected function txt($a_var)
	{
		return $this->plugin->txt($a_var);
	}

}