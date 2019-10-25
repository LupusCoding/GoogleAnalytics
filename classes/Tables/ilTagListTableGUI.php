<?php

namespace LC\ILP\GoogleAnalytics\Tables;

use \LC\ILP\GoogleAnalytics\DataObjects\Tag;
use \LC\ILP\GoogleAnalytics\DataObjects\TagCollection;

/**
 * Class ilTagListTableGUI
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilTagListTableGUI extends \ilTable2GUI
{
	/** @var \ilGoogleAnalyticsPlugin  */
	protected $pl;

	/** @var \ilLanguage  */
	protected $lng;

	/**
	 * ilTagListTableGUI constructor.
	 * @param \ilGoogleAnalyticsConfigGUI $parent_obj
	 * @param string $parent_cmd
	 */
	public function __construct(\ilGoogleAnalyticsConfigGUI $parent_obj, string $parent_cmd = 'listTags')
	{
		global $DIC;

		$this->pl = $parent_obj->getPlugin();
		$this->lng = $DIC->language();

		parent::__construct($parent_obj, $parent_cmd);

		$this->setTitle($this->pl->txt('list_of_tags'));
//		$this->setLimit(9999);

		$this->addColumns();

		$this->setEnableHeader(true);
		$this->setFormAction($DIC->ctrl()->getFormAction($parent_obj));
		$this->setRowTemplate($this->pl->getDirectory() . '/templates/tpl.table_row.html' , false);
		$this->disable("footer");
		$this->setExternalSorting(true);

		$this->prepareData();

		$this->addMultiCommand("askDeleteTag", $this->lng->txt("delete"));
	}

	/**
	 * @param Tag $a_set
	 * @return void
	 */
	protected function fillRow($a_set)
	{
		foreach ($this->getColumns() as $k => $v) {
			switch ($k) {
				case 'id':
					$this->fillCheckboxColumn('tag_id', $a_set->getId());
					break;
				case 'name':
					$this->fillColumn($a_set->getName());
					break;
				case 'type':
					$this->fillColumn($a_set->getType());
					break;
				case 'definition':
					$definition = '';
					switch ($a_set->getType()) {
						case 'udf_data':
							$udfObj = \ilUserDefinedFields::_getInstance();
							$udef = $udfObj->getVisibleDefinitions();
							if(array_key_exists($a_set->getDefinition(), $udef)) {
								$definition = $udef[$a_set->getDefinition()]['field_name'];
							}
							break;
						default:
							$definition = $a_set->getDefinition();
							break;
					}
					$this->fillColumn($definition);
					break;
				case 'actions':
					$this->ctrl->setParameter($this->parent_obj, 'tag_id', $a_set->getId());
					$this->fillActionColumn(
						$this->ctrl->getLinkTarget($this->parent_obj, 'editTag'),
						$this->lng->txt('edit')
					);
					break;
				default:
					$this->fillEmptyColumn();
					break;
			}
		}
	}

	/**
	 * @param $value
	 * @param string $array_glue
	 * @return void
	 */
	protected function fillColumn($value, $array_glue = ", "): void
	{
		$this->tpl->setCurrentBlock('td');
		$this->tpl->setVariable('VALUE', (is_array($value) ? implode($array_glue, $value) : $value));
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * @return void
	 */
	protected function fillEmptyColumn(): void
	{
		$this->tpl->setCurrentBlock('td');
		$this->tpl->setVariable('VALUE', '&nbsp;');
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * @param $key
	 * @param $value
	 * @return void
	 */
	protected function fillCheckboxColumn($key, $value): void
	{
		$this->tpl->setCurrentBlock('checkbox');
		$this->tpl->setVariable('CBNAME', $key);
		$this->tpl->setVariable('CBVAL', $value);
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * @param $link
	 * @param $text
	 * @return void
	 */
	protected function fillActionColumn($link, $text): void
	{
		$this->tpl->setCurrentBlock('action');
		$this->tpl->setVariable('ACTION_LINK', $link);
		$this->tpl->setVariable('ACTION_TEXT', $text);
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * @return array
	 */
	private function getColumns(): array
	{
		$cols = [];

		$cols['id'] = [
			'txt' => '',
			'default' => true,
			'width' => 'auto',
			'sort_field' => false,
		];
		$cols['name'] = [
			'txt' => $this->pl->txt('tag_name'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => false,
		];
		$cols['type'] = [
			'txt' => $this->pl->txt('tag_type'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => false,
		];
		$cols['definition'] = [
			'txt' => $this->pl->txt('tag_definition'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => false,
		];
		$cols['actions'] = [
			'txt' => $this->lng->txt('actions'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => false,
		];

		return $cols;
	}

	/**
	 * @return void
	 */
	protected function addColumns(): void
	{
		foreach ($this->getColumns() as $k => $v) {
			if (isset($v['sort_field'])) {
				$sort = $v['sort_field'];
			} else {
				$sort = NULL;
			}
			$this->addColumn($v['txt'], $sort, $v['width']);
		}
	}

	/**
	 * @return void
	 */
	private function prepareData(): void
	{
		$tagCollection = new TagCollection();

		if ($tagCollection->getTagCount() > 0) {
			$this->setData($tagCollection->getTags());
		} else {
			$this->setData([]);
		}
	}
}