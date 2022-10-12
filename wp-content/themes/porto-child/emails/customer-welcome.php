<?php

declare(strict_types=1);
// phpcs:disable Generic.WhiteSpace.DisallowTabIndent.TabsUsed -- mixing with HTML

get_template_part('emails/email-header');
?>

<?php
// prepare variables
$data = get_query_var('Data');
$barcode = get_field('newsletter_barcode', 'options');
$AssetsURL = get_stylesheet_directory_uri();
?>


<div style="background:#ffffff; background-color:#ffffff; margin: 0px auto; max-width: 600px; font-family: OpenSans, Arial, sans-serif; font-size:16px; line-height: 1.5; color:#494949">
    <img height="auto" src="<?= esc_url($AssetsURL) ?>/mail-top-new.jpg" style="display:block;height:auto;width:100%;margin-bottom:40px;" />
    We are Le Belge and we are a boutique chocolatier located in the iconic Napa Valley. We pride ourselves on drawing inspiration from the rolling hills of the vineyards, exemplifying decades of devotion, to the renowned wineries honing their craft.
    Here's a little welcome gift for your first order. Use the code below for 15% off:
    <br /><br /><br />

    <table style="table-layout: fixed;width: 100%;">
        <tr>
            <th>
                <img height="auto" src="<?= esc_url($AssetsURL) ?>/mail-img.jpg" style="display:block;height:auto;width:100%;" />
            </th>
            <th class="table-code-th" style="padding-left:40px;">
                <div style="font-family:OpenSans, Arial, sans-serif;font-size:30px;line-height:1;color:#707070;">
                    <span style="display:block; font-size:30px; font-weight:bold; color:#707070; text-align:left;">
                        Use the code below for 15% off
                    </span>
                </div>
                <strong style="font-weight: bold; font-weight: bold; display: block; margin-top: 40px; border: 1px dashed #8F8178; padding: 16px; font-size: 20px; width: max-content; min-width: 50%;"><?= $barcode; ?></strong>

            </th>
        </tr>
    </table>

    <br /><br />

    With a shared respect for the terroir, we emulate our regional counterparts by crafting unique, artisanal, small-batch chocolates offering a distinctive and indulgent experience that is esoteric to Napa.

    <br /><br /> &nbsp;

</div>


<?php
get_template_part('emails/email-footer');
?>