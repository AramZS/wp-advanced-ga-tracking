<?php

/*
Plugin Name: Advanced Google Analytics Tracking Tools
Plugin URI: http://cfo.com/
Description: Advanced tracking for Google Analytics.
Version: 1.0.2
Author: Aram Zucker-Scharff, Rob Flaherty
Author URI: http://cfo.com/
License: GPL2
*/

/*  Developed for the CFO

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Set up some constants
define( 'AGATT_SLUG', 'agatt' );
define( 'AGATT_TITLE', 'Advanced Google Analytics Tracking' );
define( 'AGATT_MENU_SLUG', AGATT_SLUG . '-menu' );
define( 'AGATT_ROOT', dirname(__FILE__) );
define( 'AGATT_FILE_PATH', AGATT_ROOT . '/' . basename(__FILE__) );
define( 'AGATT_URL', plugins_url('/', __FILE__) );
define( 'AGATT_VERSION', '1.0.2' );

class Advanced_Google_Analytics_Tracking {

    var $admin;
    var $headready;

    public static function init() {
		static $instance;

		if ( ! is_a( $instance, 'Advanced_Google_Analytics_Tracking' ) ) {
			$instance = new self();
		}

		return $instance;
	}

	// See http://php.net/manual/en/language.oop5.decon.php to get a better understanding of what's going on here.
	private function __construct() {

        $this->includes();

        $this->set_up_admin();
        $this->set_up_headready();
    }

	/**
	 * Include necessary files
	 *
	 * @since 1.7
	 */
	function includes() {
       require_once( AGATT_ROOT . '/includes/admin.php' );
       require_once( AGATT_ROOT . '/includes/headready.php' );
    }

    /**
     * Get a safe string for custom variables or event tracking for Google Analytics.
     */
    public function get_for_google_analytics($string){
        $string = strip_tags($string);
        $string = remove_accents( html_entity_decode($string) );
        $safe_string = esc_js( $string );
        return $safe_string;
    }
    /**
     * Echo an event tracking code for Google Analytics.
     */
    public function the_event_tracking($category, $action, $label, $value = 1, $noninteraction = false, $a_ready = true){
        if (!is_int($value)){
            $value = 1;
        }
        if (!is_bool($noninteraction)){
            $noninteraction = false;
        }
        $boolString = ($noninteraction) ? 'true' : 'false';
        $s = sprintf('_gaq.push([%1$s, %2$s, %3$s, %4$s, %5$s, %6$s]);',
            "'_trackEvent'",
            "'" . self::get_for_google_analytics($category) . "'",
            "'" . self::get_for_google_analytics($action) . "'",
            "'" . self::get_for_google_analytics($label) . "'",
            $value,
            $boolString

        );

        if ($a_ready){
            $s = 'onClick="' . $s . '"';
        }

        echo $s;
    }

    public function create_basic_jquery_ga_click_events($args){

      #Should we be minifying the output via https://github.com/tedious/JShrink instead?
      ?>
      <script type="text/javascript">
      jQuery(window).load(function() {<?php
      foreach ($args as $event){
        ?>
jQuery('body').on("click", "<?php echo $event['domElement']; ?>", function() {
          <?php self::the_event_tracking($event['category'], $event['action'], $event['label'], 1, false, false); ?>
});
        <?php
      }
      ?>});
      </script>
      <?php

    }

    function set_up_admin(){
        if ( empty( $this->admin ) ){
            $this->admin = new AGATT_Admin;
        }
    }

    function set_up_headready(){
        if ( empty( $this->headready ) ){
            $this->headready = new AGATT_HeadReady;
        }
    }

}

/**
 * Bootstrap
 *
 * You can also use this to get a value out of the global, eg
 *
 *    $foo = agatt()->bar;
 *
 * @since 0.0.1
 */
function agatt() {
	return Advanced_Google_Analytics_Tracking::init();
}

// Start me up!
agatt();

include('library/rivited/wp-plugin/riveted/riveted.php');
