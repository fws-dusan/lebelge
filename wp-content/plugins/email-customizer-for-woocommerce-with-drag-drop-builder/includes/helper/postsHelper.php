<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class
 */
class EC_Helper_Posts
{
    public function __construct()
    {
    }
    public function insert($lang, $type, $email)
    {
        $my_post=array(
              'post_type' => EC_WOO_BUILDER_POST_TYPE,
              'post_title' => $type,
              'post_content' => $email,
              'post_status' => 'draft',
              'post_name' => $lang,
              'comment_status' => 'closed',
              'ping_status' => 'closed'
          );

        wp_insert_post($my_post);
    }
    public function update($lang, $type, $email)
    {
        $my_post = array(
            'ID' =>  $this->get_email_id($lang, $type),
            'post_type' => EC_WOO_BUILDER_POST_TYPE,
            'post_title' => $type,
            'post_content' => $email,
            'post_status' => 'draft',
            'post_name' => $lang,
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        );

        wp_update_post($my_post);
    }
    public function insert_for_import($title, $content, $postName,$type,$status)
    {
      global $wpdb;
      $status = $wpdb->query(
        $wpdb->prepare("insert into $wpdb->posts (`post_type`,`post_title`,`post_content`,`post_status`,`post_name`,`comment_status`,`ping_status`) values (%s,%s,%s,%s,%s,'closed','closed')"
                      ,$type,$content,$postName,$status,$title));
      return $status;
    }
    public function update_for_import($title, $content, $postName,$type)
    {
       global $wpdb;
       $status = $wpdb->query($wpdb->prepare("Update $wpdb->posts set  `post_content` = %s where `post_title`=%s and `post_name`=%s and `post_type`=%s",
                              $content, $title, $postName, $type));
    }

    /*
    * get email content from db
    */
    public function get_saved_emails()
    {
        global $wpdb;
        $query = "select t.* from ( SELECT id,post_title,post_content,DATE_FORMAT(`post_date`, '%d-%m-%Y') as dt,post_date FROM $wpdb->posts where post_name='ec_woo_template'  and post_type='".EC_WOO_BUILDER_POST_TYPE."' ) as t order by t.dt desc,t.post_title asc";
        $result = $wpdb->get_results($query);
        return $result;
    }
    /*
    * get email content from db
    */
    public function get_email_content($lang, $type)
    {
        global $wpdb;
        $query = "SELECT post_content FROM $wpdb->posts where post_title='".$type."' and post_name='".$lang."' and post_type='".EC_WOO_BUILDER_POST_TYPE."' limit 1";
        $result = $wpdb->get_results($query);
        if (sizeof($result)!=0) {
          return $result[0]->post_content;
        }
        return "";
    }

    /*
    * check the email in the DB
    */
    public function exists_email($lang, $type)
    {
        global $wpdb;
        $query = "SELECT count(*) FROM $wpdb->posts where post_title='".$type."' and post_name='".$lang."' and post_type='".EC_WOO_BUILDER_POST_TYPE."' limit 1";
        $result = $wpdb->get_var($query);
        return $result==0?false:true;
    }

    public function exists_post($title, $name,$type)
    {
        global $wpdb;
        $query = "SELECT count(*) FROM $wpdb->posts where post_title='".$title."' and post_name='".$name."' and post_type='".$type."' limit 1";
        $result = $wpdb->get_var($query);
        return $result==0?false:true;
    }
    /*
    * check the email in the DB
    */
    public function get_email_id($lang, $type)
    {
        global $wpdb;
        $query = "SELECT ID FROM $wpdb->posts where post_title='".$type."' and post_name='".$lang."' and post_type='".EC_WOO_BUILDER_POST_TYPE."' limit 1";
        $result = $wpdb->get_var($query);
        return $result;
    }
    public function get_email_id_shortcode($title, $name)
    {
        global $wpdb;
        $query = "SELECT ID FROM $wpdb->posts where post_title='".$title."' and post_name='".$name."' and post_type='".EC_WOO_BUILDER_POST_TYPE_CUSTOM_CODE."' limit 1";
        $result = $wpdb->get_var($query);
        return $result;
    }
    /*
    * Delete all posts
    */
    public function delete_all()
    {
        global $wpdb;
        $status = $wpdb->get_var("DELETE FROM $wpdb->posts WHERE `post_type` = '".EC_WOO_BUILDER_POST_TYPE."'");
        return $status;
    }

    /*
    * Delete all posts
    */
    public function delete_saved_template($id)
    {
        global $wpdb;
        $status = $wpdb->get_var("DELETE FROM $wpdb->posts WHERE id=".$id." and  `post_type` = '".EC_WOO_BUILDER_POST_TYPE."'");
        return $status;
    }

    public static function save_settings_option($name, $value)
    {
        $option = get_option($name, '');
        if ($option == '') {
            add_option($name, $value);
        } else {
            update_option($name, $value);
        }
    }
    /*
    * Gte all email templates
    */
    public function get_all_email_templates()
    {
        global $wpdb;
        $query = "SELECT post_title,post_name,post_type,post_content FROM $wpdb->posts where post_type='".EC_WOO_BUILDER_POST_TYPE."' or post_type='".EC_WOO_BUILDER_POST_TYPE_CUSTOM_CODE."'";
        $result = $wpdb->get_results($query);
        return $result;
    }
}