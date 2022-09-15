<div class="js-popup-newsletter popup-news">
    <div class="js-popup-newsletter newsletter__popup-container">
        <img class="newsletter__image" src="<?php echo get_stylesheet_directory_uri(); ?>/image.jpg" alt="image">
        <a class="js-popup-close newsletter__popup-container-close" href="javascript:;" title="Close popup">X</a>
        <div id="newsletter__popup-content">
            <div class="newsletter__popup-contentWrapper">
                <div class="newsletter__popup-text">
                    <h3>Subscribe now and get</h3>
                    <p>15% off</p>
                </div>
                <div class="newsletter__popup-form">
                    <form method="post" id="subscribe-popup-form" class="subscribe-popup-form">
                        <div class="newsletter__popup-form-input__wrap">
                            <input class="newsletter__email js-newsletter-email" type="email" name="email" placeholder="Join our mailing list" required>
                            <input class="newsletter__submit js-newsletter-submit" type="submit" value="Join">
                            <img class="newsletter__loader js-newsletter-loader" src="<?php echo get_stylesheet_directory_uri(); ?>/loader-circle.gif" alt="image">
                        </div>
                    </form>
                </div>
                <a class="newsletter__popup-content-link js-newsletter__popup-content-link" href="javascript:;">No thanks, I'd rather pay full price!</a>
            </div>
            <span class="newsletter__popup-message js-newsletter-message"></span>
        </div>
    </div>
</div>