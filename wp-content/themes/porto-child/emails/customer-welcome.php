<?php

declare(strict_types=1);
// phpcs:disable Generic.WhiteSpace.DisallowTabIndent.TabsUsed -- mixing with HTML

get_template_part('emails/email-header');
?>

<?php
// prepare variables
$data = get_query_var('Data');
$barcode = get_field('newsletter_barcode', 'options');
?>


<div style="background:#ffffff; background-color:#ffffff; margin: 0px auto; max-width: 600px; font-family: OpenSans, Arial, sans-serif; font-size:16px; line-height: 1.5; color:#494949">
    We are Le Belge and we are a boutique chocolatier located in the iconic Napa Valley. We pride ourselves on drawing inspiration from the rolling hills of the vineyards, exemplifying decades of devotion, to the renowned wineries honing their craft.
    Here's a little welcome gift for your first order. Use the code below for 15% off:
    <br /><br />
    <strong style="font-weight: bold;"><?= $barcode; ?></strong>
    <br /><br />

    With a shared respect for the terroir, we emulate our regional counterparts by crafting unique, artisanal, small-batch chocolates offering a distinctive and indulgent experience that is esoteric to Napa.

    <br /><br /> &nbsp;

</div>


<?php
get_template_part('emails/email-footer');
?>