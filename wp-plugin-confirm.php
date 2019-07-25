<?php
/*
Plugin Name: WP Plugin Confirm
Plugin URI:
Description: Displays a confirmation dialog when plug-ins are enabled / disabled. Display plug enable / disable logs on the dashboard.
Version: 1.0.1
Author: PRESSMAN
Author URI: https://www.pressman.ne.jp/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Plugin_Confirm {

	private static $instance;

	const LOG_WIDGET_ID = 'wpc_log_widget';

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_css_js' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_action( 'init', array( $this, 'add_log' ) );
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
	}

	/**
	 * Load css, js
	 */
	public function load_css_js() {
		wp_enqueue_script( 'wpc_plugin-confirm', plugin_dir_url( __FILE__ ) . 'assets/js/plugin-confirm.js', array( 'jquery' ), false, true );
		wp_enqueue_style( 'wpc_widget-log', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
	}

	/**
	 * Add dashboard widget
	 */
	public function add_dashboard_widgets() {
		wp_add_dashboard_widget( self::LOG_WIDGET_ID, 'plugin enable / disable Logs [10 latest]', array(
			$this,
			self::LOG_WIDGET_ID
		) );
	}

	/*
	 * Display plug enable / disable log in widget
	 */
	public function wpc_log_widget() {
		$messages = '';
		if ( ! file_exists( $this->get_log_file_path() ) ) {
			echo 'There is no log.';

			return;
		}
		if ( ( $handle = fopen( $this->get_log_file_path(), "r" ) ) !== false ) {
			$row = array();
			while ( ( $data = fgetcsv( $handle ) ) ) {
				$row[] = $data;
			}
			$row = array_reverse( $row );
			foreach ( $row as $index => $data ) {
				if ( $index < 10 ) {
					$back_color = $index % 2 == 0 ? ' bkg-color' : '';
					$messages   .= "<div class='widget-log-item{$back_color}'>" .
					               "<p>{$data[0]} [{$data[1]}]</p>" .
					               "<p>{$data[2]}</p>" .
					               "</div>";
				}
			}
			fclose( $handle );
		}
		echo $messages;
	}

	/**
	 * Add plugin enable / disable log to CSV file.
	 */
	public function add_log() {
		$action = ( isset ( $_GET['action'] ) ) ? $_GET['action'] : '';
		$plugin = ( isset ( $_GET['plugin'] ) ) ? $_GET['plugin'] : '';
		if ( '' === $action || '' === $plugin ) {
			return;
		}
		$this->mkdir( dirname( __FILE__ ) . '/log/', 0700 );
		$now_datetime = date_i18n( 'Y-m-d H:i:s' );
		$data         = array( $now_datetime, $action, $plugin );
		$fp           = fopen( $this->get_log_file_path(), 'a' );
		fputcsv( $fp, $data );
		fclose( $fp );
	}

	/**
	 * Return log file name
	 *
	 * @return string
	 */
	private function get_log_file_path() {
		$filename = date_i18n( 'Y-m' ) . '.csv';

		return dirname( __FILE__ ) . '/log/' . $filename;
	}

	/**
	 * Create directory
	 *
	 * @param string $path File Path
	 * @param int $mod Permission
	 *
	 * @return bool
	 */
	private function mkdir( $path, $mod = 0700 ) {
		if ( file_exists( $path ) ) {
			return true;
		}
		if ( @mkdir( $path, $mod, true ) == false ) {
			return false;
		}
		chmod( $path, $mod );
		if ( file_exists( $path ) ) {
			return true;
		}

		return false;
	}
}

WP_Plugin_Confirm::get_instance();
