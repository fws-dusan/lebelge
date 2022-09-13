<?php
/**
 * File changes page.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$active_tab = self::get_active_tab();
$page_tabs  = self::$tabs;

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Website File Changes Monitor', 'website-file-changes-monitor' ); ?></h1>
	<nav class="nav-tab-wrapper"> 
		<?php foreach ( $page_tabs as $slug => $page_tab ) : ?>
			<a href="<?php echo esc_url( $page_tab['link'] ); ?>" class="nav-tab<?php echo $slug === $active_tab ? ' nav-tab-active' : false; ?>"><?php echo esc_html( $page_tab['title'] ); ?>
				<?php
				if ( isset( $page_tab['unread_count'] ) && absint( $page_tab['unread_count'] ) ) {
					echo '<span class="wfcm-update-plugins update-plugins"><span class="events-count">' . absint( $page_tab['unread_count'] ) . '</span></span>';
				}
				?>
			</a>
		<?php endforeach; ?>
	</nav>
	<div id="wfcm-file-changes-view" data-view="<?php echo esc_attr( $active_tab ); ?>"></div>
</div>
