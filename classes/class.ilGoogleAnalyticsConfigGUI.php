<?php

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');

use \LC\ILP\GoogleAnalytics\DataObjects\Settings;
use \LC\ILP\GoogleAnalytics\DataObjects\Tag;
use \LC\ILP\GoogleAnalytics\Tables\ilTagListTableGUI;

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

	/** @var \ilToolbarGUI */
	protected $toolbar;

	/** @var \ilTabsGUI */
	protected $tabs;

	/** @var string */
	protected $active_tab;

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
		$this->toolbar = $DIC->toolbar();
		$this->tabs = $DIC->tabs();
		$this->active_tab = '';
	}

	/**
	 * @param $cmd
	 * @return void
	 */
	public function performCommand($cmd)
	{
		$this->construct();
		$next_class = $this->ctrl->getNextClass($this);
		$this->setTabs();

		switch ($next_class) {
			default:
				switch ($cmd) {
					case 'addNewTag':
					case 'editTag':
					case 'askDeleteTag':
						$this->tabs->activateTab('listTags');
						$this->{$cmd}();
						break;
					case 'configure':
					case 'listTags':
						$this->tabs->activateTab($cmd);
						$this->{$cmd}();
						break;
					default:
						$this->{$cmd}();
						break;
				}
				break;
		}
	}

	/**
	 * @return ilGoogleAnalyticsPlugin
	 */
	public function getPlugin(): \ilGoogleAnalyticsPlugin
	{
		return $this->plugin;
	}

	/**
	 * @return array
	 */
	protected function getTabs(): array
	{
		return [
			0 => [
				'id' => 'configure',
				'txt' => $this->txt('plugin_configuration'),
				'cmd' => 'configure',
			],
			1 => [
				'id' => 'listTags',
				'txt' => $this->txt('tag_configuration'),
				'cmd' => 'listTags',
			],
		];
	}

	protected function listTags(): void
	{
		$btn = \ilLinkButton::getInstance();
		$btn->setCaption($this->txt('addNewTag'), false);
		$btn->setUrl($this->ctrl->getLinkTarget($this, 'addNewTag'));
		$this->toolbar->addButtonInstance($btn);

		$table = new ilTagListTableGUI($this);
		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * @param ilPropertyFormGUI|null $form
	 * @return void
	 */
	protected function addNewTag(\ilPropertyFormGUI $form = null): void
	{
		if (!$form) {
			$form = $this->getNewTagForm();
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function editTag(\ilPropertyFormGUI $form = null): void
	{
		if (!$form) {
			$form = $this->getNewTagForm('update');
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function getNewTagForm($mode = 'create'): \ilPropertyFormGUI
	{
		$form = new \ilPropertyFormGUI();

		if ($mode == 'create') {
			$form->setTitle($this->txt("new_tag_form"));
		} else {
			$form->setTitle($this->txt("edit_tag_form"));
		}

		/**********************************/
		/* TagManager Variable name Input */
		/**********************************/
		$name = new \ilTextInputGUI($this->txt('ga_tag_key'), 'ga_tag_key');
		$name->setInfo($this->txt('ga_tag_key_info'));
		$name->setRequired(true);

		/**********************************/
		/* Type of data Choice            */
		/**********************************/
		$type_select = new \ilRadioGroupInputGUI($this->txt('data_type'), 'data_type');
		$type_select->setRequired(true);

		// option udf data
		$ts_udf = new \ilRadioOption($this->txt('udf_data'), 'udf_data', $this->txt('udf_data_info'));
		/* UDF data Select                */
		$udf_values = new \ilSelectInputGUI($this->txt('udf_select'),'udf_select');
		$udf_values->setOptions($this->getSelectOptionsByChoice('udf_data'));
		$ts_udf->addSubItem($udf_values);
		$type_select->addOption($ts_udf);

		$type_select->setValue('udf_data');
		$form->addItem($name);
		$form->addItem($type_select);

		if ($mode == 'create') {
			$form->addCommandButton("createTag", $this->lng->txt("save"));
		} else {
			$tag_id = $_REQUEST['tag_id'];
			$tag = new Tag($tag_id);

			$name->setValue($tag->getName());
			$type_select->setValue($tag->getType());

			switch ($tag->getType()) {
				case 'udf_data':
					$udf_values->setValue($tag->getDefinition());
					break;
			}
			$type_select->setDisabled(true);

			$this->ctrl->setParameter($this, 'tag_id', $tag_id);
			$form->addCommandButton("updateTag", $this->lng->txt("save"));
		}
		$form->addCommandButton("listTags", $this->lng->txt("cancel"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * @param string $choice
	 * @return array
	 */
	protected function getSelectOptionsByChoice($choice = 'user_data'): array
	{
		$this->lng->loadLanguageModule('common');
		$this->lng->loadLanguageModule('user');
		$this->lng->loadLanguageModule('maps');
		$this->lng->loadLanguageModule('ecs');
		$options = ['none' => ''];
		switch ($choice) {
			case 'udf_data':
				/*
				 * READ:
				 * $udfObj = \ilUserDefinedFields::_getInstance();
				 * $udef = $udfObj->getVisibleDefinitions();
				 * $uddObj = new \ilUserDefinedData($event->getUsrId());
				 * $udata = $uddObj->getAll();
				 * foreach ($udef as $field_id => $definition) {
				 * 		$data[$field_id] = (isset($udata[$field_id]) ? $udata[$field_id] : NULL);
				 * }
				 */
				$udfObj = \ilUserDefinedFields::_getInstance();
				$udef = $udfObj->getVisibleDefinitions();
				if (!empty($udef)) {
					$options = [];
					foreach ($udef as $field_id => $definition) {
						$options[$field_id] = $definition['field_name'];
					}
					asort($options);
				}
				break;
		}
		return $options;
	}

	protected function createTag()
	{
		$form = $this->getNewTagForm();

		if ($form->checkInput()) {
			$tag = new Tag();
			$tag->setName($form->getInput('ga_tag_key'));
			$tag->setType($form->getInput('data_type'));
			if ($tag->getType() == 'user_data' && $form->getInput('ud_select')) {
				$tag->setDefinition($form->getInput('ud_select'));
			}
			if ($tag->getType() == 'udf_data' && $form->getInput('udf_select')) {
				$tag->setDefinition($form->getInput('udf_select'));
			}

			if ($tag->getDefinition() === '') {
				ilUtil::sendFailure($this->txt("saving_wrong_data"), true);
				$form->setValuesByPost();
				$this->tpl->setContent($form->getHtml());
				return;
			}

			$tag->save();

			ilUtil::sendSuccess($this->txt("saving_invoked"), true);
			$this->ctrl->redirect($this, "listTags");

		} else {
			$this->tabs->activateTab('listTags');
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHtml());
		}
	}

	protected function updateTag()
	{
		$tag_id = $_REQUEST['tag_id'];
		$form = $this->getNewTagForm();

		if ($form->checkInput()) {
			$tag = new Tag($tag_id);
			$tag->setName($form->getInput('ga_tag_key'));
			$tag->setType($form->getInput('data_type'));
			if ($tag->getType() == 'user_data' && $form->getInput('ud_select')) {
				$tag->setDefinition($form->getInput('ud_select'));
			}
			if ($tag->getType() == 'udf_data' && $form->getInput('udf_select')) {
				$tag->setDefinition($form->getInput('udf_select'));
			}

			if ($tag->getDefinition() === '') {
				ilUtil::sendFailure($this->txt("saving_wrong_data"), true);
				$form->setValuesByPost();
				$this->tpl->setContent($form->getHtml());
				return;
			}

			$tag->save();

			ilUtil::sendSuccess($this->txt("saving_invoked"), true);
			$this->ctrl->redirect($this, "listTags");

		} else {
			$this->tabs->activateTab('listTags');
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHtml());
		}
	}

	protected function askDeleteTag(): void
	{
		if(!$_POST["tag_id"])
		{
			ilUtil::sendFailure($this->txt("select_one"));
			$this->listTags();
			return;
		}
		$tags = $_POST['tag_id'];

		$confirmation_gui = new \ilConfirmationGUI();
		$confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
		$confirmation_gui->setHeaderText($this->txt("delete_tags_confirm"));
		$confirmation_gui->setCancel($this->lng->txt("cancel"), "listTags");
		$confirmation_gui->setConfirm($this->lng->txt("delete"), "deleteTag");

		foreach ($tags as $tag_id) {
			$tagObj = new Tag($tag_id);
			$confirmation_gui->addItem("tag_id[]", $tag_id, $tagObj->getName());
		}

		$this->tpl->setContent($confirmation_gui->getHTML());
	}

	protected function deleteTag()
	{
		if(!$_POST["tag_id"])
		{
			ilUtil::sendFailure($this->txt("err_no_data"));
			$this->listTags();
			return;
		}
		$tags = $_POST['tag_id'];

		$fail = [];
		$success = [];
		foreach ($tags as $tag_id) {
			$tag = new Tag($tag_id);
			if ($tag->delete() === false) {
				$fail[] = $tag->getName();
			} else {
				$success[] = $tag->getName();
			}
		}

		if(count($fail) > 0)
		{
			ilUtil::sendFailure(sprintf($this->txt("err_delete_names"), implode(', ', $fail)));
		}
		if (count($success) > 0) {
			ilUtil::sendSuccess(sprintf($this->txt("delete_success"), implode(', ', $success)), true);
		}
		$this->ctrl->redirect($this, "listTags");
	}

	/**
	 * @return void
	 */
	protected function configure(): void
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

		// activate tracking
		$cb = new \ilCheckboxInputGUI($this->txt('active'), 'active');
		$cb->setChecked($this->settings->getActive());
		$form->addItem($cb);

		// g token
		$ti = new \ilTextInputGUI($this->txt('ga_token'), 'token');
		$ti->setInfo($this->txt('ga_token_info'));
		$ti->setValue($this->settings->getAnalyticsToken());
		$form->addItem($ti);

		// track user
		$cb = new \ilCheckboxInputGUI($this->txt('track_user'), 'track_uid');
		$cb->setChecked($this->settings->getTrackUid());

		// gtag field for user_id
		$ti = new \ilTextInputGUI($this->txt('uid_key'), 'uid_key');
		$ti->setInfo($this->txt('uid_key_info'));
		$ti->setValue($this->settings->getUidKey());
		$cb->addSubItem($ti);

		$form->addItem($cb);

		// add noscript
		$cb = new \ilCheckboxInputGUI($this->txt('add_noscript'), 'add_noscript');
		$cb->setInfo($this->txt('add_noscript_info'));
		$cb->setChecked($this->settings->getAddNoscript());
		$form->addItem($cb);

		// optin / optout
		$rgi = new \ilRadioGroupInputGUI($this->txt('opt_in_out'), 'opt_in_out');

		// optin
		$rgo = new \ilRadioOption($this->txt('opt_in'), Settings::PL_GA_OPT_IN);
		$rgo->setInfo($this->txt('opt_in_info'));

		// optin message
		$ti = new \ilTextInputGUI($this->txt('confirm_message'), 'confirm');
		$ti->setInfo($this->txt('confirm_message_info'));
		$ti->setValue($this->settings->getConfirmMessage());
		$rgo->addSubItem($ti);

		$rgi->addOption($rgo);

		// optout
		$rgo = new \ilRadioOption($this->txt('opt_out'), Settings::PL_GA_OPT_OUT);
		$rgo->setInfo($this->txt('opt_out_info'));

		// optout snippet
		$ne = new \ilNonEditableValueGUI($this->txt('opt_out_code'), 'opt_out_code');
		$ne->setInfo($this->txt('opt_out_code_info'));
		$snippet_tpl = '<a class="ga_trackbutton" href="#"></a>';
		$ne->setValue($snippet_tpl);
		$rgo->addSubItem($ne);

		// optout deactivation message
		$ti = new \ilTextInputGUI($this->txt('sentence_active'), 'sentence_active');
		$ti->setInfo($this->txt('sentence_active_info'));
		$ti->setValue($this->settings->getSentenceActive());
		$rgo->addSubItem($ti);

		// optout activation message
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
	public function save(): void
	{
		$form = $this->getConfigurationForm();

		if ($form->checkInput()) {
			// save...
			$this->settings->setActive(($form->getInput('active') == true));
			if ($form->getInput('token')) {
				$this->settings->setAnalyticsToken($form->getInput('token'));
			}
			$this->settings->setTrackUid(($form->getInput('track_uid') == true));
			if ($form->getInput('uid_key')) {
				$this->settings->setUidKey($form->getInput('uid_key'));
			}
			$this->settings->setAddNoscript(($form->getInput('add_noscript') == true));
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

	/**
	 * @return void
	 */
	private function setTabs(): void
	{
		if (!empty($this->getTabs())) {
			foreach ($this->getTabs() as $tab) {
				$this->tabs->addTab($tab['id'], $tab['txt'], $this->ctrl->getLinkTarget($this, $tab['cmd']));
			}
		}
	}

}