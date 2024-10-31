<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');

class PeepSoReactionsInstall extends PeepSoInstall
{
	public function plugin_activation()
	{
		// Set some default settings
		#$settings = PeepSoConfigSettings::get_instance();
		#$settings->set_option('peepso_reactions_use_custom_message', 0);

		parent::plugin_activation();

		return (TRUE);
	}

	// optional DB table creation
	public static function get_table_data()
	{
		$aRet = array(
			'reactions' => "
				CREATE TABLE `reactions` (
					`reaction_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `reaction_user_id` bigint(20) unsigned NOT NULL,
					  `reaction_act_id` bigint(20) unsigned NOT NULL,
					  `reaction_type` smallint(5) unsigned NOT NULL DEFAULT '1',
					  `reaction_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  PRIMARY KEY (`reaction_id`),
					  UNIQUE KEY `module` (`reaction_user_id`,`reaction_act_id`),
					  KEY `external` (`reaction_act_id`),
					  KEY `user` (`reaction_user_id`)
				) ENGINE=InnoDB",
		);

		return $aRet;
	}
}