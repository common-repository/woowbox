<?php

/**
 * Notices admin class.
 *
 * Handles retrieving whether a particular notice has been dismissed or not,
 * as well as marking a notice as dismissed.
*
 * @package WoowBox
 * @author  Sergey Pasyuk
 */
class WoowBox_Notice {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Holds all dismissed notices
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $notices;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Populate $notices.
		$this->notices = get_option( 'woowbox_notices' );
		if ( ! is_array( $this->notices ) ) {
			$this->notices = array();
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WoowBox_Notice object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_Notice ) ) {
			self::$instance = new WoowBox_Notice();
		}

		return self::$instance;
	}

	/**
	 * Checks if a given notice has been dismissed or not
	 *
	 * @since 1.0.0
	 *
	 * @param string $notice Programmatic Notice Name.
	 *
	 * @return bool Notice Dismissed
	 */
	public function is_dismissed( $notice ) {
		if ( ! isset( $this->notices[ $notice ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Marks the given notice as dismissed
	 *
	 * @since 1.0.0
	 *
	 * @param string $notice Programmatic Notice Name.
	 */
	public function dismiss( $notice ) {
		$this->notices[ $notice ] = time();
		update_option( 'woowbox_notices', $this->notices );
	}

	/**
	 * Marks a notice as not dismissed
	 *
	 * @since 1.0.0
	 *
	 * @param string $notice Programmatic Notice Name.
	 */
	public function undismiss( $notice ) {
		unset( $this->notices[ $notice ] );
		update_option( 'woowbox_notices', $this->notices );
	}

	/**
	 * Displays an inline notice with some WOOW styling.
	 *
	 * @since 1.0.0
	 *
	 * @param string $notice         Programmatic Notice Name.
	 * @param string $title          Title.
	 * @param string $message        Message.
	 * @param string $type           Message Type (updated|warning|error) - green, yellow/orange and red respectively.
	 * @param string $button_text    Button Text (optional).
	 * @param string $button_url     Button URL (optional).
	 * @param bool   $is_dismissible User can Dismiss Message (default: true).
	 */
	public function display_inline_notice( $notice, $title, $message, $type = 'success', $button_text = '', $button_url = '', $is_dismissible = true ) {

		// Check if the notice is dismissible, and if so has been dismissed.
		if ( $is_dismissible && $this->is_dismissed( $notice ) ) {
			// Nothing to show here, return!
			return;
		}

		// Display inline notice.
		?>
		<div class="woowbox-notice <?php echo $type . ( $is_dismissible ? ' is-dismissible' : '' ); ?>" data-notice="<?php echo $notice; ?>">
			<?php
			// Title.
			if ( ! empty ( $title ) ) {
				?>
				<p class="woowbox-intro"><?php echo $title; ?></p>
				<?php
			}

			// Message.
			if ( ! empty( $message ) ) {
				?>
				<p><?php echo $message; ?></p>
				<?php
			}

			// Button.
			if ( ! empty( $button_text ) && ! empty( $button_url ) ) {
				?>
				<a href="<?php echo $button_url; ?>" target="_blank" class="button button-primary"><?php echo $button_text; ?></a>
				<?php
			}

			// Dismiss Button.
			if ( $is_dismissible ) {
				?>
				<button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">
                        <?php _e( 'Dismiss this notice', 'woowbox' ); ?>
                    </span>
				</button>
				<?php
			}
			?>
		</div>
		<?php
	}
}

// Load the notice admin class.
$woowbox_admin_notice = WoowBox_Notice::get_instance();
