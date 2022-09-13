<?php

namespace FSConditionalMethodsVendor;

/**
 * @var \WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
<th class="titledesc" scope="row">
	<label for="<?php 
echo \esc_attr($field->get_id());
?>"><?php 
echo \esc_html($field->get_label());
?>
		<?php 
if ($field->has_description_tip()) {
    ?>
			<?php 
    echo \wc_help_tip($field->get_description_tip());
    ?>
		<?php 
}
?>
	</label>
</th>
<?php 
