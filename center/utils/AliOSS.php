<?php

namespace globalunit\utils;


class AliOSS{

    private $bucket = null;
    private $endpoint = null;
    private $accessKey = null;
    private $secretKey = null;

    public function __construct($oss_config)
    {
        $this->bucket    = $oss_config["bucket"];
        $this->endpoint  = $oss_config["endpoint"];
        $this->accessKey = $oss_config["accessKey"];
        $this->secretKey = $oss_config["secretKey"];
    }

    public function put_object($content,$content_type,$object_name)
    {
        $headers = $this->_build_auth_headers('PUT', $content, $content_type, $object_name);
        $url     = "http://" . $headers['Host'] . '/' . $object_name;
        $ok = $this->_send_http_request($url,"PUT",$headers,$content);
        return $ok;
    }

    public function delete_object($object_name)
    {

    }

    public function put_bucket($bucket)
    {

    }

    public function put_bucket_acl($bucket, $acl)
    {

    }

    public function delete_bucket($bucket)
    {
    }

    public function _sign($str)
    {
        $key = base64_encode(hash_hmac("sha1",$str,$this->secretKey,$raw_output=True));
        return 'OSS '. $this->accessKey . ':' . $key;
    }

    public function _send_http_request($url, $method, $headers, $body)
    {
        $params=parse_url($url);
        var_dump($params);
        $domain=$params['host'];
        $scheme=$params['scheme'];
        $path=$params['path'];

        // $domain = $scheme."://".$domain;
        $cli = new \Swoole\Coroutine\Http\Client($domain,80);
        $cli->setHeaders($headers);
        $cli->setMethod($method);
        $cli->setData($body);
        var_dump("i am before recv $path");
        var_dump("i am before recv $domain");

        $cli->set(['timeout' => 10.0,"keep_alive"=>true,'socket_buffer_size' => 1024*1024*2,]);
        $cli->execute($path);
        $statusCode = $cli->statusCode;
        $body = $cli->body;
        var_dump("statusCode=".$statusCode);
        var_dump("body=".$body);
        $cli->close();
        return $statusCode;
    }

    public function _build_auth_headers($verb, $content, $content_type=null, $object_name, $acl=null)
    {
        $bucket        = $this->bucket;
        $endpoint      = $this->endpoint;
        $bucket_host   = "$bucket" . "."."$endpoint";
        $Date          = gmdate("D, d M Y H:i:s T");
        $aclName       = "x-oss-acl";
        $MD5           = base64_encode(md5($content,true));
        $acl           = $acl ?? 'public-read';
        $_content_type = $content_type ??  "application/octet-stream";
        $amz           = "\n$aclName:$acl";
        $resource      = "/$bucket/$object_name";
        $CL            = chr(10);
        $check_param   = $verb . $CL . $MD5 . $CL . $_content_type . $CL . $Date . $amz . $CL . $resource;

        $headers = [
            'Date'=>$Date,
            'Content-MD5'=> $MD5,
            'Content-Type'=> $_content_type,
            'Authorization'=> $this->_sign($check_param),
            'Host'=> $bucket_host,
            'Connection'=>'keep-alive',
        ];

        $headers[$aclName]=$acl;
        return $headers;
    }

}

$config=[
    "accessKey"=>"LTAIK6Ne6aVaPM9m",
    "secretKey"=>"m6U36gEVxvj40S4A3OmVSaPSuzk1oS",
    "bucket"=>"quwakuangba",
    "endpoint"=>"oss-cn-hongkong.aliyuncs.com",

];

