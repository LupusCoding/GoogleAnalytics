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