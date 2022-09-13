<?php

if (!defined('ABSPATH')) {
    exit;
}

class WooMail_Ajax
{
    private $response;
    private $menu_position;
    private $export;
    private $helper_post;
    private $log;

    private function common_functions()
    {
      if (isset($_REQUEST['development'])==false) {
        return;
      }
      EC_Helper::logger($_REQUEST['development']);
    }
    public function __construct()
    {
        $this->log=new Log(LogType::AJAX);
        $this->response=new Helper_Response();
        $this->menu_position=new Helper_Menu_Position();
        $this->export=new Helper_Export();
        $this->helper_post=new EC_Helper_Posts();
    }
    public function check_https()
    {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        return false;
    }
    /*
    * Send Test Email
    */
    public function send_email()
    {
        $response = array();
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['email'])==false && isset($posted_values['html'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        try {
            $this->common_functions();
            $mail_to = sanitize_text_field($posted_values['email']);
            $order_id= sanitize_text_field($posted_values['order_id']);
            $email_type= sanitize_text_field($posted_values['email_type']);
            $lang= sanitize_text_field($posted_values['lang']);

            $subject = 'Test Email';
            $body=stripslashes($posted_values['html']);

            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($mail_to, $subject, $body, $headers);

            $result = $this->response->success($response);
            echo $result;


            die();
        } catch (Exception $e) {
            $this->log->write('send_email', $e->getMessage());
            $error=array();
            $error['message']=$e->getMessage();
            echo $this->response->error(Helper_Response::Error, $error);
            die();
        }
    }

    /*
    * Import JSON
    */
    public function import_json()
    {
        if (isset($_FILES['import_file'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        try {
            $fileContent = file_get_contents($_FILES['import_file']['tmp_name']);
            $response = array();
            $response['data']=$fileContent;
            echo $this->response->success($response);
            die();
        } catch (Exception $e) {
            $this->log->write('import_json', $e->getMessage());
            echo $this->response->error(Helper_Response::Error);
            die();
        }
    }

    /*
    * Export JSON
    */
    public function export_json()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['json'])==false && isset($posted_values['lang'])==false && isset($posted_values['type'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $todayh = getdate();
        $type=sanitize_text_field($posted_values['type']);
        $lang=sanitize_text_field($posted_values['lang']);
        $lang=strlen($lang)!=0?$lang:strtolower(EC_Helper::get_locale());

        $file_id=$lang.'-'.$type.'-'.$todayh['seconds'].$todayh['minutes'].$todayh['hours'].$todayh['mday']. $todayh['mon'].$todayh['year'];
        $name = "/exports/".$file_id;

        $json_filename = EC_WOO_BUILDER_PATH.$name.'.json';
        $download_url = EC_WOO_BUILDER_URL.'/pages/download.php?fileid='.$file_id;

        $value=stripslashes($posted_values['json']);

        $result_save=$this->export->save_file($json_filename, $value);
        if ($result_save!=0) {
            echo $this->response->error(Helper_Response::Error);
            die();
        }

        $response= array();
        $response['url']=$download_url;
        $result = $this->response->success($response);
        echo $result;
        die();
    }
    /*
    * Export HTML
    */
    public function export_html()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['html'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $todayh = getdate();

        $type=sanitize_text_field($posted_values['type']);
        $lang=sanitize_text_field($posted_values['lang']);
        $lang=strlen($lang)!=0?$lang:strtolower(EC_Helper::get_locale());
        $name=$lang.'-'.$type.'-'.$todayh['seconds'].$todayh['minutes'].$todayh['hours'].$todayh['mday']. $todayh['mon'].$todayh['year'];

        $html_filename = EC_WOO_BUILDER_PATH.'/exports/'.$name.'.html';
        $zip_filename = EC_WOO_BUILDER_PATH.'/exports/export-'.$name.'.zip';
        $zip_url = EC_WOO_BUILDER_URL.'/exports/export-'.$name.'.zip';

        $value=stripslashes($posted_values['html']);

        $result_save=$this->export->save_file($html_filename, $value);
        if ($result_save!=0) {
            echo $this->response->error(Helper_Response::Error);
            die();
        }

        $result_zip=$this->export->create_zip($zip_filename, $html_filename, $name.'.html');
        if ($result_zip!=0) {
            echo $this->response->error(Helper_Response::Error);
            die();
        }
        $response= array();
        $response['url']=$zip_url;
        $result = $this->response->success($response);
        echo $result;
        die();
    }
    public function export_all()
    {
      $posted_values = $_REQUEST;
      $result='';

      $this->common_functions();
      $todayh = getdate();

      $file_id='woomail-all-templates-'.$todayh['seconds'].$todayh['minutes'].'-'.$todayh['mday']. $todayh['mon'].$todayh['year'];
      $name = "/exports/".$file_id;

      $json_filename = EC_WOO_BUILDER_PATH.$name.'.json';
      $download_url = EC_WOO_BUILDER_URL.'/pages/download.php?fileid='.$file_id;

      $db_records=$this->helper_post->get_all_email_templates();
      $jsonArr=array();
      foreach ($db_records as $row) {
        $jsonItem=array();
        $jsonItem['title']=$row->post_title;
        $jsonItem['name']=$row->post_name;
        $jsonItem['type']=$row->post_type;
        $jsonItem['content']=base64_encode($row->post_content);
        $jsonArr[]=$jsonItem;
      }
      $json=json_encode($jsonArr);
      $result_save=$this->export->save_file($json_filename, $json);
      if ($result_save!=0) {
          echo $this->response->error(Helper_Response::Error);
          die();
      }

      $response= array();
      $response['url']=$download_url;
      $result = $this->response->success($response);
      echo $result;
      die();
    }
    /*
    * Import JSON
    */
    public function import_all()
    {
        if (isset($_FILES['import_file'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        try {
            $jsonContent = file_get_contents($_FILES['import_file']['tmp_name']);
            $db_records=json_decode($jsonContent);

            foreach ($db_records as $row)
            {
                $post_type=$row->type;
                $lang=$row->name;
                $email_type=$row->title;
                $content=base64_decode($row->content);
                $status=$post_type==EC_WOO_BUILDER_POST_TYPE?'draft':'publish';

                $result_exist=$this->helper_post->exists_post($email_type,$lang,$post_type);
                if ($result_exist==true) {
                    $this->helper_post->update_for_import($lang, $email_type, $content,$post_type);
                } else {
                    $this->helper_post->insert_for_import($lang, $email_type, $content,$post_type,$status);
                }
            }


            $response = array();
            $response['code']=200;
            echo $this->response->success($response);
            die();
        } catch (Exception $e) {
            $this->log->write('import_all', $e->getMessage());
            echo $this->response->error(Helper_Response::Error);
            die();
        }
    }
    /*
    * Save panel position
    */
    public function save_panel_position()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['position'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $value=sanitize_text_field($posted_values['position']);
        $return=$this->menu_position->update($value);

        if ($return==0) {
            $result = $this->response->success();
        } else {
            $result = $this->response->error(Helper_Response::Error);
        }

        echo $result;
        die();
    }


    /*
    * check the language value
    */
    private function check_lang($lang)
    {
        if ($lang=='') {
            return 'en_us';
        } else {
            return $lang;
        }
    }

    /*
    * Load template
    */
    public function template_load()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['type'])==false || isset($posted_values['order_id'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }

        $this->common_functions();
        $lang=sanitize_text_field($posted_values['lang']);
        $type=sanitize_text_field($posted_values['type']);
        $order_id=sanitize_text_field($posted_values['order_id']);

        $lang=$this->check_lang($lang);

        $email_core=new WooMailCore($order_id,$type,true);
        $email_core->collectData();
        $data=$email_core->getShortcodeList();

        $shortcode_json=EC_Helper::generate_shortcode_json($email_core->getShortcodesGroupByIntegration());

        $email=$this->helper_post->get_email_content($lang, $type);

        $replace_email=EC_Helper::get_replace_email_for_type($type);

        //load_template in there
        if (!session_id()) {
            session_start();
        }

        $_SESSION['email_core'] = $email_core;

        $result=array();
        $result['shortcode_data']=$data;
        $result['shortcode_json']=$shortcode_json;
        $result['replace_email']=$replace_email;
        $result['email']=$email;

        if (extension_loaded ('newrelic')) {
            newrelic_disable_autorum();
        }

        echo $this->response->success($result);
        die();
    }


    /*
    * Save template
    */
    public function template_save()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['type'])==false && isset($posted_values['order_id'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $lang=sanitize_text_field($posted_values['lang']);
        $type=sanitize_text_field($posted_values['type']);

        $email=$posted_values['email'];
        $email=ltrim(rtrim($email, '"'),'"');

        $lang=$this->check_lang($lang);

        $result_exist=$this->helper_post->exists_email($lang, $type);

        if ($result_exist==true) {
            $this->helper_post->update($lang, $type, $email);
        } else {
            $this->helper_post->insert($lang, $type, $email);
        }
        echo $this->response->success();
        die();
    }

    /*
    * Save new template
    */
    public function template_new_save()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['email'])==false && isset($posted_values['name'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $name=sanitize_text_field($posted_values['name']);
        $email=$posted_values['email'];
        $type='ec_woo_template';

        $this->helper_post->insert($type, $name, $email);


        echo $this->response->success();
        die();
    }
    /*
    * load saved template
    */
    public function template_load_saved()
    {
        $this->common_functions();
        $posted_values = $_REQUEST;
        $saved_emails=$this->helper_post->get_saved_emails();

        $data = array();
        $data['list'] = array();
        foreach ($saved_emails as $item) {
            $data_item=array();
            $data_item['id']=$item->id;
            $data_item['name']=$item->post_title;
            $data_item['data']=$item->post_content;

            $t = strtotime($item->post_date);
            $data_item['date']=date('M d, Y. H:s', $t);

            array_push($data['list'], $data_item);
        }

        $result=array();
        $result['data']=$data;
        echo $this->response->success($result);
        die();
    }

    /*
    * delete saved template
    */
    public function template_delete_saved()
    {
        $posted_values = $_REQUEST;
        if (isset($posted_values['id'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $id=sanitize_text_field($posted_values['id']);
        $this->helper_post->delete_saved_template($id);

        echo $this->response->success();
        die();
    }

    /*
    * Save as template
    */
    public function template_save_as()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['type'])==false && isset($posted_values['order_id'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $lang=sanitize_text_field($posted_values['lang']);
        $type=sanitize_text_field($posted_values['type']);
        $email=$posted_values['email'];
        $lang=$this->check_lang($lang);

        $result_exist=$this->helper_post->exists_email($lang, $type);
        if ($result_exist==true) {
            $this->helper_post->update($lang, $type, $email);
        } else {
            $this->helper_post->insert($lang, $type, $email);
        }

        echo $this->response->success();
        die();
    }
    /*
    * save custom css
    */
    public function save_custom_css()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['custom_css'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $custom_css=sanitize_text_field($posted_values['custom_css']);
        $this->save_settings_option('ec_woo_settings_custom_css', $custom_css);

        echo $this->response->success();
        die();
    }


    /*
    * save settings
    */
    public function save_settings()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['img_width'])==false &&
            isset($posted_values['img_height'])==false &&
            isset($posted_values['show_img'])==false &&
            isset($posted_values['show_sku'])==false &&
            isset($posted_values['replace_mail'])==false &&
            isset($posted_values['rtl'])==false &&
            isset($posted_values['show_meta'])==false &&
            isset($posted_values['border_padding'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $img_width=sanitize_text_field($posted_values['img_width']);
        $img_height=sanitize_text_field($posted_values['img_height']);
        $show_img=sanitize_text_field($posted_values['show_img']);
        $show_sku=sanitize_text_field($posted_values['show_sku']);
        $replace_mail=sanitize_text_field($posted_values['replace_mail']);
        $cell_padding=sanitize_text_field($posted_values['cell_padding']);
        $rtl=sanitize_text_field($posted_values['rtl']);
        $show_custom_shortcode=sanitize_text_field($posted_values['show_custom_shortcode']);
        $show_meta=sanitize_text_field($posted_values['show_meta']);
        $show_product_link=sanitize_text_field($posted_values['show_product_link']);

        $this->save_settings_option('ec_woo_settings_border_padding', $cell_padding);
        $this->save_settings_option('ec_woo_settings_image_width', $img_width);
        $this->save_settings_option('ec_woo_settings_image_height', $img_height);
        $this->save_settings_option('ec_woo_settings_show_image', $show_img);
        $this->save_settings_option('ec_woo_settings_replace_mail', $replace_mail);
        $this->save_settings_option('ec_woo_settings_show_sku', $show_sku);
        $this->save_settings_option('ec_woo_settings_rtl', $rtl);
        $this->save_settings_option('ec_woo_settings_show_custom_shortcode', $show_custom_shortcode);
        $this->save_settings_option('ec_woo_settings_show_meta', $show_meta);
        $this->save_settings_option('ec_woo_settings_show_product_link', $show_product_link);

        $this->update_replace_mail_all($replace_mail);


        echo $this->response->success();
        die();
    }

    /*
    * save_related_items
    */
    public function save_related_items()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['columns'])==false &&
            isset($posted_values['count'])==false &&
            isset($posted_values['products_by'])==false &&
            isset($posted_values['show_price'])==false &&
            isset($posted_values['show_name'])==false &&
            isset($posted_values['show_image'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $columns=sanitize_text_field($posted_values['columns']);
        $count=sanitize_text_field($posted_values['count']);
        $products_by=sanitize_text_field($posted_values['products_by']);
        $show_price=sanitize_text_field($posted_values['show_price']);
        $show_name=sanitize_text_field($posted_values['show_name']);
        $show_image=sanitize_text_field($posted_values['show_image']);

        $this->save_settings_option('ec_woo_related_items_columns', $columns);
        $this->save_settings_option('ec_woo_related_items_count', $count);
        $this->save_settings_option('ec_woo_related_items_show_name', $show_name);
        $this->save_settings_option('ec_woo_related_items_show_price', $show_price);
        $this->save_settings_option('ec_woo_related_items_show_image', $show_image);
        $this->save_settings_option('ec_woo_related_items_product_by', $products_by);

        echo $this->response->success();
        die();
    }


    public function save_settings_replace_email_type()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['email_type'])==false &&
            isset($posted_values['replace_email_type'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $email_type=sanitize_text_field($posted_values['email_type']);
        $replace_email_type=sanitize_text_field($posted_values['replace_email_type']);
        $this->save_settings_option('ec_woo_settings_replace_mail_'.$email_type, $replace_email_type);
        echo $this->response->success();
        die();
    }
    private function update_replace_mail_all($replace_email)
    {
        global $wpdb;
        $status = $wpdb->get_var("UPDATE  $wpdb->options SET `option_value`='".$replace_email."' WHERE `option_name` like '%ec_woo_settings_replace_mail_%'");
        return $status;
    }
    private function save_settings_option($name, $value)
    {
        $option = get_option($name, '-1');
        if ($option == '-1') {
            add_option($name, $value);
        } else {
            update_option($name, $value);
        }
    }

    /*
    * activate updates
    */
    public function activate_updates()
    {

        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['purchase_code'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        $this->common_functions();
        $purchase_code=sanitize_text_field($posted_values['purchase_code']);
        $post = [
            'purchase_key' => $purchase_code,
            'web_url' => get_home_url()
        ];
        $ch = curl_init('https://emailcustomizer.com/api/install.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($ch);
        curl_close($ch);
        $json_result=json_decode($response);
        if ($json_result->code!=200) {
            $resp=array();
            $resp['code']=$json_result->code;
            $resp['message']=$json_result->message;
            echo $this->response->error(Helper_Response::Error, $resp);
            die();
        }

        $this->save_settings_option('ec_woo_purchase_code', $purchase_code);
        $this->save_settings_option('ec_woo_show_activate_updates', 'no');

        echo $this->response->success();
        die();
    }

    //TODO: remove it later. also remove $_SESSION['email_core'] instance
    /*
    * @deprecated function
    * generate shortcode
    */
    public function generate_shortcode()
    {
        $posted_values = $_REQUEST;
        $result='';
        if (isset($posted_values['shortcode'])==false) {
            echo $this->response->error(Helper_Response::Param_Error);
            die();
        }
        if (!session_id()) {
            session_start();
        }
        $this->common_functions();
        $email_core=$_SESSION['email_core'];
        if ($email_core!=null) {
          $email_core->collectData();
          $email_core->addShortcodesToWP();
          $shor=str_replace('\\', '', sanitize_text_field($posted_values['shortcode']));
          $shortcode_result= do_shortcode($shor);
          $result=array();
          $result['data']=$shortcode_result;
          echo $this->response->success($result);
        }

        die();
    }

    /*
    * skip activate updates
    */
    public function skip_activate_updates()
    {
        $this->common_functions();
        $this->save_settings_option('ec_woo_show_activate_updates', 'no');
        echo $this->response->success();
        die();
    }
}
