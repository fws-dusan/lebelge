<?php
/**
 * @package WPDesk\FS\Packages\WooCommerceSettings
 */

use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettings;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\ConditionalMethodsActionsUrls;

/**
 * @var string                        $field_id .
 * @var string                        $title .
 * @var string                        $tooltip_html .
 * @var string                        $type .
 * @var SingleRulesetSettings[]       $rulesets_settings .
 * @var string                        $add_ruleset_url .
 * @var string                        $delete_ruleset_url .
 * @var ConditionalMethodsActionsUrls $rulesets_actions_urls .
 * @var string                        $desc .
 */


?><tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $title ) . wc_help_tip( $tooltip_html ); // phpcs:ignore. ?></label>
	</th>
	<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $type ) ); ?>">
		<table class="flexible-shipping-conditional-methods-rulesets widefat" cellspacing="0">
			<thead>
				<tr>
					<th class="sort"></th>
					<th class="name"><?php echo esc_html( __( 'Name', 'flexible-shipping-conditional-methods' ) ); ?></th>
					<th class="status"><?php echo esc_html( __( 'Enabled', 'flexible-shipping-conditional-methods' ) ); ?></th>
				</tr>
			</thead>
			<tbody class="flexible-shipping-conditional-methods-rulesets-rows">
			<?php if ( count( $rulesets_settings ) ) : ?>
				<?php foreach ( $rulesets_settings as $ruleset ) : ?>
				<tr>
					<td class="ruleset-sort" width="1%">
						<input type="hidden" name="<?php echo esc_attr( ( (string) $field_id ) . '[]' ); ?>" value="<?php echo esc_attr( (string) $ruleset->get_id() ); ?>">
					</td>
					<td class="column-primary">
						<a href="<?php echo esc_attr( $rulesets_actions_urls->prepare_single_ruleset_settings_url( $ruleset->get_id() ) ); ?>"><strong><?php echo esc_html( $ruleset->get_name() ); ?></strong></a>
						<div class="row-actions">
							<span class="edit">
								<a href="<?php echo esc_url( $rulesets_actions_urls->prepare_single_ruleset_settings_url( $ruleset->get_id() ) ); ?>" title="<?php echo esc_attr( __( 'Edit ruleset', 'flexible-shipping-conditional-methods' ) ); ?>">
									<?php echo esc_html( __( 'Edit', 'flexible-shipping-conditional-methods' ) ); ?>
								</a>
								|
							</span>
							<span class="trash">
								<a href="<?php echo esc_url( $rulesets_actions_urls->prepare_delete_ruleset_url( $ruleset->get_id() ) ); ?>" title="<?php echo esc_attr( __( 'Delete ruleset', 'flexible-shipping-conditional-methods' ) ); ?>">
									<?php echo esc_html( __( 'Delete', 'flexible-shipping-conditional-methods' ) ); ?>
								</a>
							</span>
						</div>
					</td>
					<td>
						<?php if ( 'yes' === $ruleset->get_enabled() ) : ?>
							<?php echo esc_html( __( 'Yes', 'flexible-shipping-conditional-methods' ) ); ?>
						<?php else : ?>
							<?php echo esc_html( __( 'No', 'flexible-shipping-conditional-methods' ) ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="3" class="no-rows">
						<?php echo esc_html( __( 'Add first ruleset', 'flexible-shipping-conditional-methods' ) ); ?>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<a href="<?php echo esc_attr( $add_ruleset_url ); ?>" class="button flexible-shipping-conditional-methods-add-ruleset"><?php echo esc_html( __( 'Add ruleset', 'flexible-shipping-conditional-methods' ) ); ?></a>
					</td>
				</tr>
			</tfoot>
		</table>
		<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
	</td>
</tr>
