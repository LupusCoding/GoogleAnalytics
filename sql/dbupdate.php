<#1>
<?php
if(!$ilDB->tableExists('ganalytics_urel'))
{
	$ilDB->createTable('ganalytics_urel', [
		'user_id' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		],
		'ga_uid' => [
			'type'     => 'text',
			'length'   => 255,
			'notnull' => true,
			'default' => 0
		],
		'ga_track' => [
			'type'     => 'integer',
			'length'   => 1,
			'notnull' => false,
			'default' => 0
		],
		'updated_at' => [
			'type'     => 'timestamp',
			'notnull' => true,
			'default' => ''
		],
	]);
	$ilDB->addPrimaryKey('ganalytics_urel', array('user_id'));
}
?>
<#2>
<?php
$ilDB->addIndex('ganalytics_urel', array('ga_uid'), 'i1');
?>
<#3>
<?php
if(!$ilDB->tableExists('ganalytics_tags'))
{
	$ilDB->createTable('ganalytics_tags', [
		'id' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		],
		'name' => [
			'type'     => 'text',
			'length'   => 255,
			'notnull' => true,
			'default' => 0
		],
		'type' => [
			'type'     => 'text',
			'length'   => 255,
			'notnull' => true,
			'default' => 0
		],
		'definition' => [
			'type'     => 'text',
			'length'   => 255,
			'notnull' => true,
			'default' => 0
		],
	]);
	$ilDB->addPrimaryKey('ganalytics_tags', array('id'));
	$ilDB->createSequence('ganalytics_tags');
	$ilDB->addIndex('ganalytics_tags', array('name'), 'i1');
}
?>
