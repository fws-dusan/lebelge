<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationNewAccount implements IWooMailIntegration
{
  private $name="New Account";
  private $shortcodes;
  private $args;

  public function __construct($args)
  {
    $this->args=$args;
  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();
    global $wpdb,$wp_hasher;
    $key = wp_generate_password(20, false);
    do_action('retrieve_password_key', $this->args['email']->user_login, $key);

    $this->shortcodes['user_name'] = $this->args['email']->user_login;
    $this->shortcodes['user_email'] = $this->args['email']->user_email;
    if (isset($this->args['email']->user_pass) && !empty($this->args['email']->user_pass)) {
        $this->shortcodes['user_password'] = $this->args['email']->user_pass;
    }

    $this->shortcodes['user_activation_link'] = $this->getActivationURL($this->args['email']->user_email,$key);

    return $this->shortcodes;
  }
  private function getActivationURL($userEmail,$key)
  {
    global $wpdb,$wp_hasher;
    $userId=$this->getUserId($userEmail);

    if ( empty( $wp_hasher ) ) {
        require_once ABSPATH . 'wp-includes/class-phpass.php';
        $wp_hasher = new PasswordHash( 8, true );
    }
    $user_activation = time() . ':' . $wp_hasher->HashPassword( $key );

    $activation=get_user_meta( $userId, '_woocommerce_activation', true );
    if( $activation ){
      $user_activation = $activation;
    }else {
      $wpdb->update($wpdb->users, array('user_activation_key' => $user_activation), array('ID' => $userId));
      update_user_meta( $userId, '_woocommerce_activation', $user_activation );
    }

    $activation_url = wc_get_page_permalink('myaccount')."?activate=$user_activation&suffix=yes";
    return $activation_url;
  }

  private function getUserId($userEmail)
  {
    $user=get_user_by('email' ,$userEmail);
    if(!isset($user))
    {
       EC_Validation::checkNullOrEmpty($user,'WMIntegrationNewAccount->$user ');
    }
    return $user->ID;
  }
}