<?php

require_once ('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once ('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/GoogleAnalytics/classes/Views/class.ilGoogleAnalyticsAsyncGUI.php');

use \LC\ILP\GoogleAnalytics\DataObjects\Settings;
use \LC\ILP\GoogleAnalytics\DataObjects\UserRelations;
use \LC\ILP\GoogleAnalytics\DataObjects\TagCollection;

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

					$html = $a_par['html'];
					$index = strripos($html, "</head>", -7);
					if ($index !== false && $settings->getTrackUid()) {
						$tracking = '';
						$opt = '';
						$tag_snippet = '';

						if ($this->isTrackableUser($DIC->user())) {
							// Tag collection
							$tagCollection = new TagCollection();
							if ($tagCollection->count() > 0) {
								$tag_snippet = $this->getTagSnippetsByCollection($tagCollection, $DIC->user());
							}

							// Tracking script
							$tracking = $this->getTagManagerHtml($settings, $DIC->user(), $tag_snippet);

							if ($settings->getOptInOut() === Settings::PL_GA_OPT_IN) {
								// optIn script
								$opt = $this->getOptInHtml($settings);
							} else if ($settings->getOptInOut() === Settings::PL_GA_OPT_OUT) {
								// optIn script
								$opt = $this->getOptOutHtml($settings);
							}
						}

						$ns = '';
						if ($settings->getAddNoscript() === true) {
							// noscript
							$ns = $this->getNoScriptHtml($settings);
						}

						$html = substr($html, 0, $index) . $tracking . $opt . $ns . substr($html, $index);
						return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $html);
					}

				}
			}
		}
		return ['mode' => ilUIHookPluginGUI::KEEP, 'html' => ''];
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
	protected function isSpecificGUI(string $class_name): bool
	{
		global $DIC;

		return (count(array_filter($DIC->ctrl()->getCallHistory(), function (array $history) use ($class_name): bool {
			return (strtolower($history["class"]) === strtolower($class_name));
		})) > 0);
	}

	/**
	 * @param ilObjUser $user
	 * @return bool
	 */
	private function isTrackableUser(\ilObjUser $user): bool
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
	private function getTagManagerHtml(Settings $settings, \ilObjUser $user, string $tag_snippet = ''): string
	{
		$async_link = \ilGoogleAnalyticsAsyncGUI::getEntryLink('setflag');
		$user_relation = new UserRelations();
		$user_relation->loadById($user->getId());

		/** @var \ilTemplate $tpl */
		$tpl = $this->plugin_object->getTemplate('tpl.analytics_gtm.html', true, true);
		$tpl->setVariable('ga_token', $settings->getAnalyticsToken());

		if ($this->isTrackableUser($user)) {

			if ($settings->getOptInOut() === Settings::PL_GA_OPT_OUT) {
				if ($user_relation->getGaTrack() === null) {
					$user_relation->setGaTrack(true)->save();
					$tpl->setVariable('ut', 'true');
				} else {
					$tpl->setVariable('ut', ($user_relation->getGaTrack() ? 'true' : 'false'));
				}
			} else {
				if ($user_relation->getGaTrack() === null) {
					$user_relation->setGaTrack(false)->save();
					$tpl->setVariable('ut', 'false');
				} else {
					$tpl->setVariable('ut', ($user_relation->getGaTrack() ? 'true' : 'false'));
				}
			}

			$tpl->setVariable('user_key', $settings->getUidKey());
			$tpl->setVariable('user_id', $user_relation->getGaUid());
			$tpl->setVariable('GA_OPTIONAL_TAGS', $tag_snippet);;
			$tpl->setVariable('ajax_link', $async_link);
		}

		return $tpl->get();
	}

	/**
	 * @param Settings $settings
	 * @return string
	 * @throws ilTemplateException
	 */
	private function getNoScriptHtml(Settings $settings): string
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
	private function getOptInHtml(Settings $settings): string
	{
		/** @var \ilTemplate $opt_tpl */
		$opt_tpl = $this->plugin_object->getTemplate('tpl.analytics_optin.html', true, true);
		$opt_tpl->setVariable('lifetime', $settings->getCookieLifetime());
		$opt_tpl->setVariable('user_track_confirm', $settings->getConfirmMessage());
		return $opt_tpl->get();
	}

	/**
	 * @param Settings $settings
	 * @return string
	 * @throws ilTemplateException
	 */
	private function getOptOutHtml(Settings $settings): string
	{
		/** @var \ilTemplate $snippet_tpl */
		$snippet_tpl = $this->plugin_object->getTemplate('tpl.analytics_agreement.html', true, true);
		$snippet_tpl->setVariable('sentence_active', $settings->getSentenceActive());
		$snippet_tpl->setVariable('sentence_inactive', $settings->getSentenceInactive());
		return $snippet_tpl->get();
	}

	/**
	 * @param TagCollection $collection
	 * @return string
	 * @throws ilTemplateException
	 */
	private function getTagSnippetsByCollection(TagCollection $collection, \ilObjUser $user): string
	{
		/** @var \ilTemplate $snippet_tpl */
		$snippet_tpl = $this->plugin_object->getTemplate('tpl.analytics_gtags.html', true, true);
		foreach ($collection->getTags() as $tag) {
			$definition = '';
			switch ($tag->getType()) {
				case 'udf_data':
					$uddObj = new \ilUserDefinedData($user->getId());
					$definition = $uddObj->get("f_".$tag->getDefinition());
					break;
				default:
					continue;
					break;
			}
			$snippet_tpl->setCurrentBlock('tag_definition');
			$snippet_tpl->setVariable('TAG_KEY', $tag->getName());
			$snippet_tpl->setVariable('TAG_VAL', $definition);
			$snippet_tpl->parseCurrentBlock();
		}
		return $snippet_tpl->get();
	}
}