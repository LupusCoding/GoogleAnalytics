<?php

require_once ('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');

use \LC\ILP\GoogleAnalytics\DataObjects\Settings;

/**
 * Class ilGoogleAnalyticsUIHookGui
 * @author Ralph Dittrich <dittrich.ralph@lupuscoding.de>
 */
class ilGoogleAnalyticsUIHookGui extends ilUIHookPluginGUI
{
	const TABS_PART = "tabs";

	/**
	 * @param $a_comp
	 * @param $a_part
	 * @param array $a_par
	 * @return array|void
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		/* modify html */
		global $DIC;
		if ($a_part == "template_load" && !$DIC->ctrl()->isAsynch()) {
			if (strtolower($a_par['tpl_id']) == "tpl.main.html") {

				$settings = new Settings();
				if ($settings->getActive()) {

					$html = $a_par['html'];
					$index = strripos($html, "</head>", -7);
					if ($index !== false) {
						/** @var \ilTemplate $tpl */
						$tpl = $this->plugin_object->getTemplate('tpl.analytics_gtm.html', true, true);
						// @ToDo differ between tag manager and standard analytics

						$tpl->setVariable('ga_token', $settings->getAnalyticsToken());
						if ($settings->getTrackUid() && $this->isUserLoggedIn($DIC->user())) {
							$tpl->setCurrentBlock('track_user');
							$tpl->setVariable('user_key', $settings->getUidKey());
							$tpl->setVariable('user_id', $DIC->user()->getId());
							$tpl->setVariable('user_track_confirm', $settings->getConfirmMessage());
							$tpl->parseCurrentBlock();
						}
//						$tpl->setVariable('ga_token_conf', $settings->getAnalyticsToken());
						$tpl->setVariable('ga_token_noscript', $settings->getAnalyticsToken());

						$html = substr($html, 0, $index) . $tpl->get() . substr($html, $index);
						return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $html);
					}

				}
			}
		}
	}

	/**
	 * @param $a_comp
	 * @param $a_part
	 * @param array $a_par
	 * @return void
	 */
	public function modifyGUI($a_comp, $a_part, $a_par = [])
	{}

	/**
	 * @param string $class_name
	 * @return bool
	 */
	protected function isSpecificGUI(string $class_name): bool {
		global $DIC;
		return (count(array_filter($DIC->ctrl()->getCallHistory(), function (array $history, string $class_name): bool {
				return (strtolower($history["class"]) === strtolower($class_name));
			})) > 0);
	}

	private function isUserLoggedIn(\ilObjUser $user)
	{
		return (
			!$user->isAnonymous() &&
			$user->getId() !== 0
		);
	}
}