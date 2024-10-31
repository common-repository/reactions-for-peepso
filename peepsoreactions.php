<?php
/**
 * Plugin Name: PeepSo Reactions
 * Plugin URI: http://mattsplugins.io
 * Description: Extends "like" on posts with "reactions" such as: loving, sad, laughing
 * Author: Matt Jaworski
 * Author URI: http://mattsplugins.io
 * Version: 1.1.1
 * Copyright: (c) 2016 MattsPlugins.io
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepsoreactions
 * Domain Path: /languages
 *
 * This software contains GPLv2 or later software courtesy of PeepSo.com, Inc
 *
 * PeepSo Reactions is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * PeepSo Reactions is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY. See the
 * GNU General Public License for more details.
 */


class PeepSoReactions
{
	private static $_instance = NULL;

	public $reactions;

	const TABLE = 'peepso_reactions';

	const PLUGIN_NAME	 = 'PeepSo Reactions';
	const PLUGIN_VERSION = '1.1.1';
	const PLUGIN_RELEASE = ''; //ALPHA1, BETA1, RC1, '' for STABLE

	const PEEPSO_VER_MIN = '1.5.0';
	const PEEPSO_VER_MAX= '1.6.0';

	private function __construct()
	{
		add_action('peepso_init', array(&$this, 'init'));
		add_action('plugins_loaded', array(&$this, 'load_textdomain'));

		if (is_admin()) {
			add_action('admin_init', array(&$this, 'check_peepso'));
		}

		register_activation_hook(__FILE__, array(&$this, 'activate'));
	}

	/**
	 * Loads the translation file for the PeepSo plugin
	 */
	public function load_textdomain()
	{
		$path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;
		load_plugin_textdomain('peepsoreactions', FALSE, $path);

		$this->reactions = array(

			0 => array(
				'label' 	=> __('Like','peepsoreactions'),
				'action' 	=> __('liked','peepsoreactions'),
			),

			1 => array(
				'label'		=> __('Love','peepsoreactions'),
				'action'	=> __('loved','peepsoreactions'),
			),

			2 => array(
				'label'		=> __('Haha','peepsoreactions'),
				'action'	=> __('laughed','peepsoreactions'),

			),

			3 => array(
				'label'		=> __('Wink','peepsoreactions'),
				'action'	=> __('winked at','peepsoreactions'),
			),

			4 => array(
				'label'		=> __('Wow','peepsoreactions'),
				'action'	=> __('gasped at','peepsoreactions'),
			),

			5 => array(
				'label'		=> __('Sad','peepsoreactions'),
				'action'	=> __('is sad about','peepsoreactions'),
			),

			6 => array(
				'label'		=> __('Angry','peepsoreactions'),
				'action'	=> __('is angry about','peepsoreactions'),
			),
		);
	}

	public function reaction( $react_id )
	{
		return (object) $this->reactions[$react_id];
	}

	public static function get_instance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new self();
		}
		return (self::$_instance);
	}

	public function init()
	{
		PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
		PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

		if (is_admin()) {
			add_action('admin_init', array(&$this, 'check_peepso'));
			add_filter('peepso_admin_config_tabs', array(&$this, 'admin_config_tabs'));
		} else {
			if( 1 == PeepSo::get_option('reactions_enable',0)) {
				add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

				add_filter('peepso_post_before_comments', array(&$this, 'action_peepso_post_before_comments'));
				add_filter('peepso_modal_before_comments', array(&$this, 'action_peepso_post_before_comments'));

				add_action('peepso_activity_post_actions', array(&$this, 'filter_peepso_activity_post_actions'), 1);
			}
		}
	}


	public function check_peepso()
	{
		if (!class_exists('PeepSo'))
		{
			if (is_plugin_active(plugin_basename(__FILE__))) {
				// deactivate the plugin
				deactivate_plugins(plugin_basename(__FILE__));
				// display notice for admin
				add_action('admin_notices', array(&$this, 'disabled_notice'));
				if (isset($_GET['activate'])) {
					unset($_GET['activate']);
				}
			}
			return (FALSE);
		}

		return (TRUE);
	}

	public function activate()
	{
		if (!$this->check_peepso()) {
			return (FALSE);
		}

		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
		$install = new PeepSoReactionsInstall();
		$res = $install->plugin_activation();
		if (FALSE === $res) {
			// error during installation - disable
			deactivate_plugins(plugin_basename(__FILE__));
		}

		return (TRUE);
	}

	public function disabled_notice()
	{
		echo '<div class="error fade">';
		echo
		'<strong>' , self::PLUGIN_NAME , ' ' ,
		__('plugin requires the PeepSo plugin to be installed and activated.', 'peepsoreactions'),
		'</a>',
		'</strong>';
		echo '</div>';
	}



	public function enqueue_scripts()
	{
		wp_enqueue_style('peepsoreactions', plugin_dir_url(__FILE__) . 'assets/css/reactions.css', array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_style('twa', plugin_dir_url(__FILE__) . 'assets/css/twa.css', array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_script('peepsoreactions', plugin_dir_url(__FILE__) . 'assets/js/reactions.js', array('peepso'), self::PLUGIN_VERSION, TRUE);
	}

	/*
	 * Methods below are used solely as an integration with the PeepSo admin section
	 */

	/**
	 * Registers a tab in the PeepSo Config Toolbar
	 * PS_FILTER
	 *
	 * @param $tabs array
	 * @return array
	 */
	public function admin_config_tabs( $tabs )
	{
		$tabs['reactions'] = array(
				'label' => __('Reactions', 'peepsoreactions'),
				'tab' => 'reactions',
				'description' => __('Reactions Config Tab', 'peepsoreactions'),
				'function' => 'PeepSoConfigSectionReactions',
		);

		return $tabs;
	}

	public function filter_peepso_activity_post_actions( $args )
	{
		$act_id = $args['post']->act_id;

		$class[] = 'ps-reaction-toggle--'.$act_id;

		$my_reaction = $this->my_reaction($act_id);

		if( is_numeric($my_reaction) ) {
			$class []= ' liked ';
			$class []= 'ps-reaction-emoticon-'.$my_reaction;

			$label = $this->reaction($my_reaction)->label;
		} else {
			$class []= 'ps-reaction-emoticon-0';

			$label = $this->reaction(0)->label;
		}

		$class = implode(' ', $class);

		$acts = array(
				'like' => array(
						'href' => '#reactions-options',
						'label' => $label,
						'class' => $class,
						'icon' => 'reaction',
						'click' => 'return reactions.action_reactions(this, ' . $act_id . ');',
						'count' => 0, // probably not important
				),
		);

		unset($args['acts']['like']);
		$args['acts'] = array_merge($acts, $args['acts']);

		return $args;
	}

	public function action_peepso_post_before_comments()
	{
		global $post;
		$post_id = $post->ID;
		$act_id = $post->act_id;

		$my_reaction = $this->my_reaction($post->act_id);

		?>
		<div id="act-reactions-<?php echo $act_id; ?>"
			 class="ps-reactions cstream-reactions-options ps-js-act-reactions-options--<?php echo $act_id; ?>"
			 data-count="">
			<ul class="ps-reaction-options">
				<?php
				foreach ($this->reactions as $react_id => $reaction) :

					$class = array(
							'ps-reaction-option',
							'ps-reaction-option--'.$act_id,
							'ps-reaction-emoticon-'.$react_id,
							'ps-reaction-option-'.$react_id.'--'.$act_id,
					);

					if( $my_reaction === $react_id ) {
						$class[]='ps-reaction-option-selected';
					}

					$class = implode(' ', $class);
					?>
					<li>
						<a class="<?php echo $class;?>" href="#react"
						   title="<?php echo $this->reaction($react_id)->label; ?>"
						   onclick="return reactions.action_react(this, <?php echo $act_id; ?>, <?php echo $post->ID;?>, <?php echo $react_id; ?>)">
						</a>
					</li>
				<?php endforeach; ?>

				<?php
				$class = array(
						'ps-reaction-option',
						'ps-reaction-option-delete',
						'ps-reaction-option--'.$act_id,
						'twa-heavy-multiplication-x',
						'ps-reaction-option-delete--'.$act_id,
				);


				if(!is_numeric($my_reaction)) {
					$class[] = 'ps-reaction-option-hidden';
				}

				$class = implode (' ', $class);
				?>

				<li class="ps-reaction-option-delete--<?php echo $act_id;?>">
					<a class="<?php echo $class;?>" href="#react-delete"
					   title="<?php echo __('Remove','peepsoreactions'); ?>"
					   onclick="return reactions.action_react_delete(this, <?php echo $act_id; ?>, <?php echo $post->ID;?>)">
					   <i class="icon-cancel"></i>
					</a>
				</li>

			</ul>
		</div>
		<?php
		$class = array(
				'ps-stream-status',
				'cstream-reactions',
				'ps-js-act-reactions--'.$act_id,
		);

		$reactions_html = $this->reactions_html($act_id);

		if (FALSE === $reactions_html) {
			$class []='ps-stream-reactions-hidden';
		}

		$class = implode(' ', $class);

		?>
		<div id="act-react-<?php echo $act_id; ?>"
			 class="ps-reaction-likes <?php echo $class;?>  " data-count="">
			<?php echo $reactions_html; ?>
		</div>
		<?php
	}

	public function my_reaction( $act_id )
	{
		$user_id = PeepSo::get_user_id();

		$activity 		= PeepSoActivity::get_instance()->get_activity($act_id);
		$module_id 		= $activity->act_module_id;
		$like = new PeepSoLike();
		$like = $like->user_liked($activity->act_external_id, $module_id, $user_id);

		if( TRUE === $like ) {
			return 0;
		}

		global $wpdb;

		// has my reaction?
		$sql = "SELECT `reaction_type` FROM `{$wpdb->prefix}" . self::TABLE . "` "
				. " WHERE `reaction_act_id`=%d "
				. " AND `reaction_user_id`=%d ";

		$sql = $wpdb->prepare($sql, $act_id, PeepSo::get_user_id());

		$res = $wpdb->get_var($sql);

		if (is_numeric($res)) {
			return (intval($res));
		}

		return (FALSE);
	}

	public function user_reaction_reset( $act_id )
	{
		global $wpdb;

		$user_id = PeepSo::get_user_id();

		// remove like
		$activity 		= PeepSoActivity::get_instance()->get_activity($act_id);
		$module_id 		= $activity->act_module_id;

		$like = new PeepSoLike();
		$like->remove_like($activity->act_external_id, $module_id, $user_id);

		// remove reaction
		$sql = "DELETE FROM `{$wpdb->prefix}" . self::TABLE . "` "
				. " WHERE `reaction_act_id`=%d "
				. " AND `reaction_user_id`=%d ";

		$sql = $wpdb->prepare($sql, $act_id, PeepSo::get_user_id());
		$wpdb->query($sql);
	}

	public function user_reaction_set( $act_id, $react_id )
	{
		$react_id = intval($react_id);

		$act_id = intval($act_id);
		$user_id = PeepSo::get_user_id();

		$peepso_activity = PeepSoActivity::get_instance();

		$activity = $peepso_activity->get_activity($act_id);
		$module_id = $activity->act_module_id;

		// post
		$act_post = $peepso_activity->get_activity_post($act_id);
		$post_id = $act_post->ID;
		$owner_id = $peepso_activity->get_author_id($post_id);

		$user = new PeepSoUser($user_id);
		$user_owner = new PeepSoUser($owner_id);
		$do_notify = FALSE;

		if( $owner_id != $user_id ) {
			$do_notify = TRUE;
			// notification data
			$mailq_data = array(
					'permalink' => PeepSo::get_page('activity') . '?status/' . $act_post->post_title,
					'post_content' => $act_post->post_content,
			);

			$mailq_data = array_merge($mailq_data, $user->get_template_fields('from'), $user_owner->get_template_fields('user'));

			// the post/activity type
			$post_type = get_post_type($post_id);
			$post_type_object = get_post_type_object($post_type);
			$activity_type = $post_type_object->labels->activity_type;

			$note = new PeepSoNotifications();
		}

		// perform like
		if( 0 == $react_id ) {
			$like = new PeepSoLike();
			$like->add_like($activity->act_external_id, $module_id, PeepSo::get_user_id());

			if( TRUE === $do_notify ) {
				// send LIKE email
				PeepSoMailQueue::add_message($owner_id, $mailq_data, sprintf(__('Someone Liked your %s', 'peepso'), $activity_type), 'like_post', 'like_post', PeepSoActivity::MODULE_ID);

				// add LIKE notification
				$notification_message = sprintf(__('Likes your %s', 'peepso'), $post_type_object->labels->activity_type);
				$note->add_notification($user_id, $owner_id, $notification_message, 'like_post', $module_id, $post_id);
			}
			return TRUE;
		}

		// perform reaction
		global $wpdb;

		$data = array(
				'reaction_user_id' => PeepSo::get_user_id(),			// user_id adding the like
				'reaction_act_id' => $act_id,							// id of peepso_activities item
				'reaction_type' => $react_id,
		);

		$wpdb->insert($wpdb->prefix . self::TABLE, $data);

		if( TRUE == $do_notify ) {
			// send REACT email
			$message_title = __('Someone', 'peepsoreactions')
					. ' ' . $this->reaction($react_id)->action
					. ' ' . sprintf(__('your %s', 'peepsoreactions'), $activity_type);

			PeepSoMailQueue::add_message($owner_id, $mailq_data, $message_title, 'like_post', 'like_post', PeepSoActivity::MODULE_ID);

			// add REACT notification
			$notification_message = ucfirst($this->reaction($react_id)->action) . ' ' . sprintf(__('your %s', 'peepsoreactions'), $post_type_object->labels->activity_type);
			$note->add_notification($user_id, $owner_id, $notification_message, 'like_post', $module_id, $post_id);
		}

		return( TRUE );
	}

	public function get_reactions_count( $act_id, $react_id )
	{
		if( 0 == $react_id) {
			$activity 		= PeepSoActivity::get_instance()->get_activity($act_id);
			$module_id 		= $activity->act_module_id;
			$like = PeepSoActivity::get_instance()->get_like_status($activity->act_external_id, $module_id);
			return $like['count'];
		}

		global $wpdb;
		$sql = "SELECT COUNT(*) FROM `{$wpdb->prefix}" . self::TABLE . "` " .
				" WHERE `reaction_act_id`=%d ";

		$sql .= " AND `reaction_type`=%d ";
		$sql = $wpdb->prepare($sql, $act_id, $react_id);


		$res = $wpdb->get_var($sql);
		return (intval($res));
	}

	public function reactions_html( $act_id )
	{
		$total_reactions = 0;

		$my_reaction = $this->my_reaction($act_id);

		foreach($this->reactions as $react_id => $reaction) {

			$count = $this->get_reactions_count( $act_id, $react_id );

			if( $count > 0) {
				$reactions[$react_id] = $count;
				$total_reactions += $count;
			}
		}

		if( 0 === $total_reactions ) {
			return FALSE;
		}

		ob_start();
		$i=0;
		arsort($reactions);

		foreach($reactions as $react_id => $reaction_count) {

			$class = array();
			$class[]='ps-reactions-count-icon';
			$class[]='ps-reaction-emoticon-'.$react_id;

			if(0==$i) {
				$class[] = 'ps-reactions-count-icon-first';
				$i++;
			}
			$class=implode(' ', $class);

			$title=array();
			$title[]=$this->reaction($react_id)->label;
			$title[]='('.$reaction_count.')';

			$title=implode(' ', $title);
			?>
			<span class="<?php echo $class;?>" title="<?php echo $title;?>"></span>
			<?php
		}
		?>

		<a href="javascript:void(0)"
		   onclick="return reactions.action_reactions_html_details(this, <?php echo $act_id; ?>)">

		<?php
		if( FALSE !== $my_reaction ) {

			echo __('You', 'peepsoreactions');
			$total_reactions--;

			if($total_reactions> 0) {
				echo " + " , $total_reactions , ' ';
				echo _n('other','others',$total_reactions,'peepsoreactions');
			}

		} else {
			echo $total_reactions , ' ';
			echo _n('person','people',$total_reactions,'peepsoreactions');
		}

		echo '</a>';
		$res = ob_get_clean();
		return $res;
	}



	public function reactions_html_details( $act_id )
	{
		ob_start();
		$reactions = array();

		$my_id = PeepSo::get_user_id();

		?>
		<div class="ps-reactions-details">
			<a id="ps-reaction-details-close" class="ps-reaction-details-close" href="javascript:void(0)"
			   onclick="return reactions.action_reactions_html(this, <?php echo $act_id; ?>)">
			   <i class="ps-icon-caret-up"></i>
			</a>
		<?php
		// User ids for  likes
		$reactions_count = array(0=>0);
	 	$activity = PeepSoActivity::get_instance()->get_activity($act_id);
        $like = new PeepSoLike();
        $names = $like->get_like_names($activity->act_external_id, $activity->act_module_id);

        if (count($names) > 0) {
            foreach ($names as $name) {
            	$reactions[0][]=$name->ID;
            	$reactions_count[0]++;
			}
		}

		// User ids for each reactions
		global $wpdb;
		foreach($this->reactions as $react_id => $reaction) {

			if(0==$react_id) {
				continue;
			}

			$reactions_count = array_merge($reactions_count, array($react_id=>0));

			$sql = "SELECT reaction_user_id  FROM `{$wpdb->prefix}" . self::TABLE . "` "
				 . " WHERE `reaction_act_id`=%d "
				 . " AND `reaction_type`=%d ";

			$sql = $wpdb->prepare($sql, $act_id, $react_id);

			$result = $wpdb->get_results($sql);

			if(count($result)) {
				foreach($result as $user) {
					$reactions[$react_id][]=$user->reaction_user_id;
					$reactions_count[$react_id]++;
				}
			}
		}

		// Most pop[ular reactions on top
		arsort($reactions_count);

		foreach($reactions_count as $react_id=>$count) {
			// array is sorted descending - abort the loop when encountering the first zero
			if(0==$count) {
				break;
			}

			$class = array();
			$class[]='ps-reactions-count-icon';
			$class[]='ps-reaction-emoticon-'.$react_id;
			$class[] = 'ps-reactions-count-icon-first';
			$class=implode(' ', $class);
			?>

			<div class="ps-reactions-details-item">
				<span class="<?php echo $class;?>"><?php echo $this->reaction($react_id)->label;?> (<?php echo $count;?>):</span>
				<span class="users">
			<?php


			$html_names = array();

			if(in_array($my_id, $reactions[$react_id])) {
				$user = new PeepSoUser($my_id);
				$html_names[] = '<a class="ps-comment-user" href="' . $user->get_profileurl() . '">' . __('You','peepsoreactions') . '</a>';

			}

			foreach($reactions[$react_id] as $user_id) {
				if ($user_id == $my_id) {
					continue;
				}
			    $user = new PeepSoUser($user_id);
                $html_names[] = '<a class="ps-comment-user" href="' . $user->get_profileurl() . '">' . $user->get_fullname() . '</a>';
            }

            echo implode(', ', $html_names);
			echo '</span>';
			echo '</div>';
		}


		$res = ob_get_clean();
		$res .="</div>";
		return $res;



	}
}

PeepSoReactions::get_instance();
// EOF