<?php

class PeepSoReactionsAjax implements PeepSoAjaxCallback
{
	private static $_instance = NULL;

	private function __construct() {}

	public static function get_instance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new self();
		}
		return (self::$_instance);
	}

	public function react(PeepSoAjaxResponse $resp)
	{
		$input = new PeepSoInput();

		$act_id = $input->post_int('act_id');

		$react_id = $input->post_int('react_id');

		$reactions = PeepSoReactions::get_instance();
		$class = 'ps-reaction-emoticon-'.$react_id;
		$label = $reactions->reaction($react_id)->label;

		// remove like + all reactions for this content and this user

		$reactions->user_reaction_reset( $act_id );

		$reactions->user_reaction_set( $act_id, $react_id );

		$resp->success(TRUE);
		$resp->set('reaction_mine_id', $react_id);
		$resp->set('reaction_mine_label', $label);
		$resp->set('reaction_mine_class', $class);

		$resp->set('reactions_html', $reactions->reactions_html( $act_id ));
	}

	public function react_delete(PeepSoAjaxResponse $resp)
	{
		$input = new PeepSoInput();

		$act_id = $input->post_int('act_id');

		$reactions = PeepSoReactions::get_instance();
		$class = 'ps-reaction-emoticon-0';
		$label = $reactions->reaction(0)->label;

		// remove like + all reactions for this content and this user
		$reactions->user_reaction_reset( $act_id );


		$resp->success(TRUE);
		$resp->set('reaction_mine_id', false);
		$resp->set('reaction_mine_label', $label);
		$resp->set('reaction_mine_class', $class);

		$resp->set('reactions_html', $reactions->reactions_html( $act_id ));
	}

	public function reactions_html(PeepSoAjaxResponse $resp)
	{
		$input = new PeepSoInput();

		$act_id = $input->post_int('act_id');

		$reactions = PeepSoReactions::get_instance();
		$resp->success(TRUE);
		$resp->set('reactions_html', $reactions->reactions_html( $act_id ));
	}

	public function reactions_html_details(PeepSoAjaxResponse $resp)
	{
		$input = new PeepSoInput();

		$act_id = $input->post_int('act_id');

		$reactions = PeepSoReactions::get_instance();

		$resp->success(TRUE);
		$resp->set('reactions_html', $reactions->reactions_html_details( $act_id ));
	}
}