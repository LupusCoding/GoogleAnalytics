<?php

require_once ('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once ('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/GoogleAnalytics/classes/Views/class.ilGoogleAnalyticsAsyncGUI.php');

use \LC\ILP\GoogleAnalytics\DataObjects\Settings;
use \LC\ILP\GoogleAnalytics\DataObjects\UserRelations;

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

			$settings = new Settings();
			if ($settings->getActive()) {

				if (strtolower($a_par['tpl_id']) == "tpl.main.html") {

					if ($this->isSpecificGUI('ilAdministrationGUI')) {
						// admin pages should not be tracked
					} else if ($this->isSpecificGUI('ilStartUpGUI')) {
						$html = $a_par['html'];
						$index = strripos($html, "</head>", -7);
						if ($index !== false && $settings->getTrackUid()) {

							if ($this->isUserLoggedIn($DIC->user())) {
								// Tracking script
								$tracking = $this->getTagManagerHtml($settings, $DIC->user());
							}
						}

						// noscript
						$ns = $this->getNoScriptHtml($settings);

						$html = substr($html, 0, $index) . $tracking . $ns . substr($html, $index);
						return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $html);

					} else {
						$html = $a_par['html'];
						$index = strripos($html, "</head>", -7);
						if ($index !== false && $settings->getTrackUid()) {

							if ($this->isUserLoggedIn($DIC->user())) {
								// Tracking script
								$tracking = $this->getTagManagerHtml($settings, $DIC->user());

								$opt = '';
								if ($settings->getOptInOut() === Settings::PL_GA_OPT_IN) {
									// optIn script
									$opt = $this->getOptInHtml($settings);
								}
							}

							// noscript
							$ns = $this->getNoScriptHtml($settings);

							$html = substr($html, 0, $index) . $tracking . $opt . $ns . substr($html, $index);
							return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $html);
						}

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

		return (count(array_filter($DIC->ctrl()->getCallHistory(), function (array $history) use ($class_name): bool {
			return (strtolower($history["class"]) === strtolower($class_name));
		})) > 0);
	}

	/**
	 * @param ilObjUser $user
	 * @return bool
	 */
	private function isUserLoggedIn(\ilObjUser $user)
	{
		return (
			!$user->isAnonymous() &&
			$user->getId() !== 0 &&
			$user->getId() !== 6
		);
	}

	/**
	 * @param Settings $settings
	 * @param ilObjUser $user
	 * @return string
	 * @throws ilTemplateException
	 */
	private function getTagManagerHtml(Settings $settings, \ilObjUser $user)
	{
		$async_link = \ilGoogleAnalyticsAsyncGUI::getEntryLink('setflag', true);
		$user_relation = new UserRelations();
		$user_relation->loadById($user->getId());

		/** @var \ilTemplate $tpl */
		$tpl = $this->plugin_object->getTemplate('tpl.analytics_gtm.html', true, true);
		$tpl->setVariable('ga_token', $settings->getAnalyticsToken());

		if ($this->isUserLoggedIn($user) /*&& $user_relation->isTrackable()*/) {

			$tpl->setVariable('ut', (
				$user_relation->getGaTrack() === null ?
				($settings->getOptInOut() === Settings::PL_GA_OPT_OUT ? 'true' : 'false') :
				($user_relation->getGaTrack() ? 'true' : 'false')
			));

			$tpl->setVariable('user_key', $settings->getUidKey());
			$tpl->setVariable('user_id', $user_relation->getGaUid());
			$tpl->setVariable('ajax_link', $async_link);
		}

		return $tpl->get();
	}

	/**
	 * @param Settings $settings
	 * @return string
	 * @throws ilTemplateException
	 */
	private function getNoScriptHtml(Settings $settings)
	{
		/** @var \ilTemplate $ns_tpl */
		$ns_tpl = $this->plugin_object->getTemplate('tpl.analytics_noscript.html', true, true);
		$ns_tpl->setVariable('ga_token_noscript', $settings->getAnalyticsToken());
		return $ns_tpl->get();
	}

	/**
	 * @param Settings $settings
	 * @return string
	 * @throws ilTemplateException
	 */
	private function getOptInHtml(Settings $settings)
	{
		/** @var \ilTemplate $opt_tpl */
		$opt_tpl = $this->plugin_object->getTemplate('tpl.analytics_optin.html', true, true);
		$opt_tpl->setVariable('lifetime', $settings->getCookieLifetime());
		$opt_tpl->setVariable('user_track_confirm', $settings->getConfirmMessage());
		return $opt_tpl->get();
	}

	/**
	 * @deprecated
	 * @return string
	 * @throws ilTemplateException
	 */
	private function getOptOutHtml(Settings $settings)
	{
		/** @var \ilTemplate $opt_tpl */
		$opt_tpl = $this->plugin_object->getTemplate('tpl.analytics_optout.html', true, true);
		$opt_tpl->setVariable('ukey', $settings->getUidKey());
		return $opt_tpl->get();
	}
}