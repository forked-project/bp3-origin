<?php

    /**
     * 高可用显示文件大小
     * @param $byte 字节
     * @return $hstr 高可用大小字符串
     */
    function height_show_size($byte){
        $hstr = '';
        if($byte<1024){
         $hstr = $byte.'B';
        }elseif($byte<1048576){
         $num=$byte/1024;
         $hstr = number_format($num,2).'kB';
        }elseif($byte<1073741824){
         $num=$byte/1048576;
         $hstr = number_format($num,2).'MB';
        }elseif($byte<1099511627776){
         $num=$byte/1073741824;
         $hstr = number_format($num,2)."GB";
        }
        return $hstr;
    }
    
    /**
     * 获取当前页面的Url绝对地址
     */ 
    function getPageUrl(){
        $page_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";
        return $page_url;
    }
    
    
    /**
     * 获取token，并可以自动更新
     * @param $config 配置参数数组
     * @param $refresh_php 刷新地址的绝对路径
     * @return false|access_token
     * 如果返回false，则说明执行了刷新token，请重新加载config文件
     * if(get_token_refresh(...)) { require(...config.php )}
     * 注意别使用 require_once 加载config.php文件
     */
     function get_token_refresh($refresh_php){
        //自动刷新token
        global $config;
        
        if($config['identify']['access_token']){
            $pass_time = time()-$config['identify']['conn_time'];
            $express_time = $config['identify']['expires_in']-$pass_time;
            if($express_time<1728000){ //有效期小于20天，自动刷新token
                $arrContextOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ];
                @file_get_contents($refresh_php,false,stream_context_create($arrContextOptions));
                return false;
            }
        }
        return $config['identify']['access_token'];
     }
    /**
     * 获取当前页面的目录的url地址
     * 获取方式：getDirUrl(basename(__FILE__));
     */ 
     function getDirUrl($fileName){
         $pageUrl = getPageUrl();
         $dirUrl = substr($pageUrl,0,-strlen($fileName));
         return $dirUrl;
     }
    /**
     * 获取网站根目录，但需要手写当前目录与根目录的前缀
     */ 
    function get_base_url($endstr){
        $pageUrl = getPageUrl();
        $base_url = str_cut_end($pageUrl,$endstr);
        
        return $base_url;
    }
     // 拷贝参数，不常用
    function setCurl(&$ch, array $header)
    { // 批处理 curl
    	$a = curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略证书
    	$b = curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 不检查证书与域名是否匹配（2为检查）
    	$c = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 以字符串返回结果而非输出
    	$d = curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 请求头
    	return ($a && $b && $c && $d);
    }
    // 拷贝参数，不常用
    function head(string $url, array $header)
    { // 获取响应头
    	$ch = curl_init($url);
    	setCurl($ch, $header);
    	curl_setopt($ch, CURLOPT_HEADER, true); // 返回响应头
    	curl_setopt($ch, CURLOPT_NOBODY, true); // 只要响应头
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    	$response = curl_exec($ch);
    	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); // 获得响应头大小
    	$result = substr($response, 0, $header_size); // 根据头大小获取头信息
    	curl_close($ch);
    	return $result;
    }
    /**
     * 强制登录，其中开启session必须在第一行填写：  session_start();
     * 在强制登录页面，填写：    force_login();  // 强制登录
     */ 
     function force_login(){
        if(empty($_SESSION['user'])){
            echo '{"error":"user not login"}';
            die;
        }
     }
     
     /**
      * 强制传递指定get参数
      * @param param_name 参数名称（字符串）
      * 例如，必须传递名为path的get参数，则：$path = force_get_param("path");
      */
     function force_get_param($param_name){
        $param = $_GET[$param_name];
        if(empty($param)){
            echo '{"error":"miss thie get type param, param_name is： '.$param_name.'"}';
            die;
        }
        return $param;
     }
     /**
      * 强制传递指定post参数
      * @param param_name 参数名称（字符串）
      * 例如，必须传递名为path的post参数，则：$path = force_post_param("path");_
      */ 
     function force_post_param($param_name){
        $param = $_POST[$param_name];
        if(empty($param)){
            echo '{"error":"miss thie post type param, param_name is： '.$param_name.'"}';
            die;
        }
        return $param;
     }
     
     /**
      * 如果file_get_content失败，则输出提示
      * 无需参数，无返回值，需要首行开启session，否则不输出错误提示
      */ 
      function errmsg_file_get_content(){
        global $http_response_header;
        if(empty($_SESSION['user'])){
            // 请求失败且未登录
            if(!strstr($http_response_header[0],"200")){
                echo '{"error":"http request error!"}';
                die;
            }
        }else if(!strstr($http_response_header[0],"200")){
            // 请求失败，但已登录
            echo '{"error":"http request error!"}';
            echo '<br>';
            var_dump($http_response_header);
            die;
        }
        
      }
      
        /**
         * 解码后重新进行url编码，以消除js编码带来的诡异错误
         * 非必要不使用js进行urlencode，而使用php进行urlencode
         * @param $param 使用js编码后的变量
         * @return $encode 使用php解码后重新编码的变量
         */ 
        function re_urlencode($param){
            
            $decode = urldecode($param);
            $encode = urlencode($decode);
            
            return $encode;
        }
        
        /**
         * 消除一个str的部分str，从后往前开始
         * 比如当前文件地址 xx文件前置目录xx/apps/abc.php 
         * 这里的xx文件前置目录xx，表示在服务器中的前置目录
         * 从后往前消掉，可在任意php文件中取得bp3根目录所在服务器目录
         * @param $srcstr 原字符串
         * @param $endstr 去掉的字符串
         * @return $newstr 新字符串
         */ 
        function str_cut_end($srcstr,$endstr){
            
            $endlength = strlen($endstr);
            $newstr = substr($srcstr,0,-$endlength);
            
            return $newstr;
        }
        
        /**
         * 快速保存全局config变量到config.php文件中
         * @param $config_file 调用函数的页面相对config.php的地址
         * 例如，config.php在父目录中，则 "../config.php"
         * 注意！！！请确保全局config变量不为空，否则将清空config.php
         */ 
        function save_config($config_file){
            global $config;        
            $text='<?php $config='.var_export($config,true).';'; 
            file_put_contents($config_file,$text);
        }

?>