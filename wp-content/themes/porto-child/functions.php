<?php

require_once('CPT_Email_signup.php');
require_once('newsletter-ajax.php');

function insert_html_in_header()
{
    require_once('newsletter-popup.php');
};

/* Front End */
add_action('wp_head', 'insert_html_in_header');

wp_enqueue_style("newsletter-popup", get_stylesheet_directory_uri() . '/' . 'newsletter-popup.css');


// Auto uncheck "Ship to a different address"
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');

// Run shortcodes in widgets
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'porto-style-css-child',
        get_stylesheet_uri(),
        array('porto-style', 'porto-dynamic-style', 'porto-theme', 'porto-theme-shop')
    );
});

//Add script to head
add_action('wp_head', function () { ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <script> (function(){ var s = document.createElement('script'); var h = document.querySelector('head') || document.body; s.src = 'https://acsbapp.com/apps/app/dist/js/app.js'; s.async = true; s.onload = function(){ acsbJS.init({ statementLink : '', footerHtml : '', hideMobile : false, hideTrigger : false, disableBgProcess : false, language : 'en', position : 'right', leadColor : '#146FF8', triggerColor : '#146FF8', triggerRadius : '50%', triggerPositionX : 'right', triggerPositionY : 'bottom', triggerIcon : 'people', triggerSize : 'bottom', triggerOffsetX : 20, triggerOffsetY : 20, mobile : { triggerSize : 'small', triggerPositionX : 'right', triggerPositionY : 'bottom', triggerOffsetX : 20, triggerOffsetY : 20, triggerRadius : '20' } }); }; h.appendChild(s); })();</script>
    <script>
        (function() {
            setTimeout(function() {
                const html = document.querySelector('html');

                const shippingTrigger = document.querySelector('.shipping-popup-trigger');
                const shippingPopup = document.querySelector('.single-product__shipping-popup-hidden');
                const shippingBox = document.querySelector('.single-product__shipping-popup-hidden-inner-box');
                const closeShippingPopup = document.querySelector('.single-product__shipping-popup-hidden-close');

                const guaranteeTrigger = document.querySelector('.guarantee-popup-trigger');
                const guaranteePopup = document.querySelector('.single-product__guarantee-popup-hidden');
                const guaranteeBox = document.querySelector('.single-product__guarantee-popup-hidden-inner-box');
                const closeGuaranteePopup = document.querySelector('.single-product__guarantee-popup-hidden-close ');

                if (shippingTrigger || closeShippingPopup || guaranteeTrigger || closeGuaranteePopup) {
                    shippingTrigger.addEventListener('click', function() {
                        if (!shippingPopup.classList.contains('is-active')) {
                            shippingPopup.classList.add('is-active');
                            html.classList.add('prevent-scroll');
                        }
                    });
                    closeShippingPopup.addEventListener('click', function() {
                        shippingPopup.classList.remove('is-active');
                        html.classList.remove('prevent-scroll');
                    });
                    guaranteeTrigger.addEventListener('click', function() {
                        if (!guaranteePopup.classList.contains('is-active')) {
                            guaranteePopup.classList.add('is-active');
                            html.classList.add('prevent-scroll');
                        }
                    });
                    closeGuaranteePopup.addEventListener('click', function() {
                        guaranteePopup.classList.remove('is-active');
                        html.classList.remove('prevent-scroll');
                    });
                }
            }, 3000);
        })()
    </script>
    <script>
        function getCookie(hiddenNewsletter) {
        let name = hiddenNewsletter + "=";
        let ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
            c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
            }
        }
        return "";
        }

        (function() {
            const popup = document.querySelector('.js-popup-newsletter');
            const activeClass = 'popup-news--active';
            const cookie = getCookie("hiddenNewsletter");

            setTimeout(() => {
                if(popup) {
                    if(localStorage.getItem('popupState') === 'shown' || cookie) {
                        popup.classList.remove(activeClass);
                    }else {
                        localStorage.setItem('popupState', 'shown');
                        popup.classList.add(activeClass);
                    }
                }
            }, 2000);

            //Open popup
            setTimeout(function() {
                const closeBtn = document.querySelector('.js-popup-close');
                const forgetBtn = document.querySelector('.js-newsletter__popup-content-link');

                if (popup) {

                    //Close popup
                    closeBtn.addEventListener('click', function() {
                        popup.classList.remove(activeClass);
                    });
                    forgetBtn.addEventListener('click', function() {
                        document.cookie = `hiddenNewsletter=true; expires=Fri, 31 Dec 9999 23:59:59 GMT`;
                        popup.classList.remove(activeClass);
                    });
                }
            }, 2000);
        })()
    </script>


    <script>
        const $ = jQuery.noConflict();

        $(".js-newsletter-submit").on('click', (event) => {
            event.preventDefault();
            const formData = {
                email: $(".js-newsletter-email").val(),
                action: 'newsletter_emails',
            };
            $('.js-newsletter-loader').addClass('active');

            $.ajax({
                url: window.ajaxurl,
                type: 'POST',
                dataType: 'JSON',
                data: formData,
                success: function(response) {
                    if (response.data.success === true) {
                        $('.js-newsletter-message').removeClass('fail').addClass('success');
                        $('.js-newsletter-message').html(response.data.message);
                        $('.js-newsletter-loader').removeClass('active');

                        document.cookie = `hiddenNewsletter=true; expires=Fri, 31 Dec 9999 23:59:59 GMT`;

                        setTimeout(() => {
                            // $('#subscribe-popup-form').submit();
                            $('.popup-news').removeClass('popup-news--active');
                        }, 3500);
                    } else {
                        $('.js-newsletter-message').addClass('fail');
                        $('.js-newsletter-message').html(response.data.message);
                        $('.js-newsletter-loader').removeClass('active');
                    }

                }
            });
        });
    </script>

<?php });

//Add options page
if (function_exists('acf_add_options_page')) {
    acf_add_options_page();
}

//Add field to product page
add_action('woocommerce_single_product_summary', 'shoptimizer_custom_author_field', 10);

function shoptimizer_custom_author_field()
{ ?>
    <?php if (get_field('shipping_policy', 'option')) { ?>
        <div class="shipping-policy">
            <p><?php the_field('shipping_policy', 'option'); ?></p>
        </div>
    <?php }
}

//Add links and popups
function add_popup_and_link()
{ ?>
    <?php if (get_field('shipping_popup_title', 'option') || get_field('shipping_popup_text', 'option') || get_field('guarantee_popup_title', 'option') || get_field('guarantee_popup_text', 'option')) { ?>
        <div class="popup-links">
            <a class="shipping-popup-link shipping-popup-trigger" href="javascript:;" style="display: block;"><?php the_field('shipping_popup_title', 'option'); ?></a>
            <a class="shipping-popup-link guarantee-popup-trigger" href="javascript:;" style="display: block;"><?php the_field('guarantee_popup_title', 'option'); ?></a>
        </div>

        <div class="single-product__shipping-popup-hidden js-single-product__shipping-popup-hidden">
            <div class="single-product__shipping-popup-hidden-inner-box">
                <span class="single-product__shipping-popup-hidden-title"><?php the_field('shipping_popup_title', 'option'); ?></span>
                <p class="single-product__shipping-popup-hidden-desc"><?php the_field('shipping_popup_text', 'option'); ?></p>
                <span class="single-product__shipping-popup-hidden-close js-single-product__shipping-popup-hidden-close">x</span>
            </div>
        </div>

        <div class="single-product__guarantee-popup-hidden js-single-product__guarantee-popup-hidden">
            <div class="single-product__guarantee-popup-hidden-inner-box">
                <span class="single-product__guarantee-popup-hidden-title"><?php the_field('guarantee_popup_title', 'option'); ?></span>
                <p class="single-product__guarantee-popup-hidden-desc"><?php the_field('guarantee_popup_text', 'option'); ?></p>
                <span class="single-product__guarantee-popup-hidden-close js-single-product__guarantee-popup-hidden-close">x</span>
            </div>
        </div>
<?php }
}

add_filter('woocommerce_share', 'add_popup_and_link', 3);

//Move cart above description
remove_action(
    'woocommerce_single_product_summary',
    'woocommerce_template_single_add_to_cart',
    30
);
add_action(
    'woocommerce_single_product_summary',
    'woocommerce_template_single_add_to_cart',
    15
);

//Pardot tracking code
function pardot_tracking_code() {
	<script type="text/javascript">

    piAId = '1008492';

    piCId = '78812';

    piHostname = 'pi.pardot.com';

    

    (function() {

                function async_load(){

                            var s = document.createElement('script'); s.type = 'text/javascript';

                            s.src = ('https:' == document.location.protocol ? 'https://pi' : 'http://cdn') + '.pardot.com/pd.js';

                            var c = document.getElementsByTagName('script')[0]; c.parentNode.insertBefore(s, c);

                }

                if(window.attachEvent) { window.attachEvent('onload', async_load); }

                else { window.addEventListener('load', async_load, false); }

    })();

    </script>
}
add_action( 'wp_footer', 'pardot_tracking_code' );