<?php

class Helper_Response
{
    const Success = 200;
    const Error = '500';
    const Param_Error= '400';

    private $messages;
    public function __construct()
    {
        $this->messages = array();
        $this->messages[Helper_Response::Error] = 'Has an error!';
        $this->messages[Helper_Response::Success] = 'Success!';
        $this->messages[Helper_Response::Param_Error] = 'Missed Parameter! Please check the request parameters';
    }

    public function error($code,$arr=array())
    {
        $response= array();
        $response['code']=$code;
        if (isset($arr) && empty($arr)==false) {
          $response = array_merge($response, $arr);
        }else {
          $response['message']=$this->messages[$code];
        }


        return json_encode($response);
    }
    public function success($arr=array())
    {
        //  print_r($arr);
        $response= array();
        $response['code']=Helper_Response::Success;
        $response['message']=$this->messages[Helper_Response::Success];
        if (isset($arr) && empty($arr)==false) {
            $response=  array_merge($response, $arr);
        }

        return json_encode($response);
    }
}