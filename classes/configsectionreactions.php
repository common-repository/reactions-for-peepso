<?php

class PeepSoConfigSectionReactions extends PeepSoConfigSectionAbstract
{
	// Builds the groups array
	public function register_config_groups()
	{
		$this->context = 'left';
		$this->group_general();

		$this->context = 'right';
		$this->group_acknowledgements();
	}


	/**
	 * General Settings Box
	 */
	private function group_general()
	{
		// # Message Enabled
		$this->set_field(
			'reactions_enable_description',
			__('Switch this on to enable the Reactions integration', 'peepsoreactions'),
			'message'
		);


		// # Enabled
		$this->set_field(
			'reactions_enable',
			__('Enable Reactions', 'peepsoreactions'),
			'yesno_switch'
		);

		$this->set_group(
			'reactions_group_general',
			__('General', 'peepsoreactions')
		);
	}

	/**
	 * Acknowledgements Box
	 */
	private function group_acknowledgements()
	{

		$this->set_field(
			'reactions_acknowledgements1_description',
			'PeepSo Reactions was developed with love at <a href="http://mattsplugins.io" target="_blank">Matt\'s plugins</a> by <a href="http://jwr.sk" target="_blank">Matt Jaworski</a>.',
			'message'
		);

		$this->set_field(
			'reactions_acknowledgements2_description',
			'This plugin uses icons and CSS code courtesy of <a href="https://github.com/ellekasai/twemoji-awesome" target="_blank">Twitter Emoji Awesome</a>.',
			'message'
		);

		$this->set_field(
			'reactions_acknowledgements3_description',
			'Base PeepSo API library based on <a href="https://github.com/PeepSo/peepso-tools-helloworld" target="_blank">PeepSo Hello World</a> courtesy of <a href="http://peepso.com" target="_blank">PeepSo, Inc</a>.',
			'message'
		);

		$this->set_group(
			'reactions_group_acknowledgements',
			__('Acknowledgements', 'peepsoreactions')
		);
	}
}