<?php
namespace globalunit\utils;

class JWTUtil
{
   public static $leeway = 0;
   public static $timestamp = null;
   public static $supported_algs = array(
       'HS256' => array('hash_hmac', 'SHA256'),
       'HS512' => array('hash_hmac', 'SHA512'),
       'HS384' => array('hash_hmac', 'SHA384'),
       'RS256' => array('openssl', 'SHA256'),
       'RS384' => array('openssl', 'SHA384'),
       'RS512' => array('openssl', 'SHA512'),
   );

    /**
     * @param $jwt  jwt字符串
     * @param $key  加密key
     * @param array $allowed_algs 加密字典
     * @return mixed
     * @throws \Exception
     */
   public static function decode($jwt, $key, array $allowed_algs = array())
   {
       $timestamp = is_null(static::$timestamp) ? time() : static::$timestamp;
       if (empty($key)) {
           throw new \Exception('Key may not be empty');
       }
       $tks = explode('.', $jwt);
       if (count($tks) != 3) {
           throw new \Exception('Wrong number of segments');
       }
       list($headb64, $bodyb64, $cryptob64) = $tks;
       if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
           throw new \Exception('Invalid header encoding');
       }
       if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
           throw new \Exception('Invalid claims encoding');
       }
       if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
           throw new \Exception('Invalid signature encoding');
       }
       if (empty($header->alg)) {
           throw new \Exception('Empty algorithm');
       }
       if (empty(static::$supported_algs[$header->alg])) {
           throw new \Exception('Algorithm not supported');
       }
       if (!in_array($header->alg, $allowed_algs)) {
           throw new \Exception('Algorithm not allowed');
       }
       if (is_array($key) || $key instanceof \ArrayAccess) {
           if (isset($header->kid)) {
               if (!isset($key[$header->kid])) {
                   throw new \Exception('"kid" invalid, unable to lookup correct key');
               }
               $key = $key[$header->kid];
           } else {
               throw new \Exception('"kid" empty, unable to lookup correct key');
           }
       }
       // Check the signature
       if (!static::verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
           throw new \Exception('Signature verification failed');
       }
       // Check if the nbf if it is defined. This is the time that the
       // token can actually be used. If it's not yet that time, abort.
       if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
           throw new \Exception(
               'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->nbf)
           );
       }
       // Check that this token has been created before 'now'. This prevents
       // using tokens that have been created for later use (and haven't
       // correctly used the nbf claim).
       if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
           throw new \Exception(
               'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->iat)
           );
       }
       // Check if this token has expired.
       if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
           throw new \Exception('Expired token');
       }
       return $payload;
   }

    /**
     * @param $payload  加密体
     * @param $key      加密key
     * @param string $alg  加密字典
     * @param null $keyId
     * @param null $head
     * @return string
     * @throws \Exception
     */
   public static function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null)
   {
       $header = array('typ' => 'JWT', 'alg' => $alg);
       if ($keyId !== null) {
           $header['kid'] = $keyId;
       }
       if ( isset($head) && is_array($head) ) {
           $header = array_merge($head, $header);
       }
       $segments = array();
       $segments[] = static::urlsafeB64Encode(static::jsonEncode($header));
       $segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));
       $signing_input = implode('.', $segments);
       $signature = static::sign($signing_input, $key, $alg);
       $segments[] = static::urlsafeB64Encode($signature);
       return implode('.', $segments);
   }

   public static function sign($msg, $key, $alg = 'HS256')
   {
       if (empty(static::$supported_algs[$alg])) {
           throw new \Exception('Algorithm not supported');
       }
       list($function, $algorithm) = static::$supported_algs[$alg];
       switch($function) {
           case 'hash_hmac':
               return hash_hmac($algorithm, $msg, $key, true);
           case 'openssl':
               $signature = '';
               $success = openssl_sign($msg, $signature, $key, $algorithm);
               if (!$success) {
                   throw new \Exception("OpenSSL unable to sign data");
               } else {
                   return $signature;
               }
       }
   }

   private static function verify($msg, $signature, $key, $alg)
   {
       if (empty(static::$supported_algs[$alg])) {
           throw new \Exception('Algorithm not supported');
       }
       list($function, $algorithm) = static::$supported_algs[$alg];
       switch($function) {
           case 'openssl':
               $success = openssl_verify($msg, $signature, $key, $algorithm);
               if ($success === 1) {
                   return true;
               } elseif ($success === 0) {
                   return false;
               }
               // returns 1 on success, 0 on failure, -1 on error.
               throw new \Exception(
                   'OpenSSL error: ' . openssl_error_string()
               );
           case 'hash_hmac':
           default:
               $hash = hash_hmac($algorithm, $msg, $key, true);
               if (function_exists('hash_equals')) {
                   return hash_equals($signature, $hash);
               }
               $len = min(static::safeStrlen($signature), static::safeStrlen($hash));
               $status = 0;
               for ($i = 0; $i < $len; $i++) {
                   $status |= (ord($signature[$i]) ^ ord($hash[$i]));
               }
               $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));
               return ($status === 0);
       }
   }

   public static function jsonDecode($input)
   {
       if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
           /** In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
            * to specify that large ints (like Steam Transaction IDs) should be treated as
            * strings, rather than the PHP default behaviour of converting them to floats.
            */
           $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
       } else {
           /** Not all servers will support that, however, so for older versions we must
            * manually detect large ints in the JSON string and quote them (thus converting
            *them to strings) before decoding, hence the preg_replace() call.
            */
           $max_int_length = strlen((string) PHP_INT_MAX) - 1;
           $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
           $obj = json_decode($json_without_bigints);
       }
       if (function_exists('json_last_error') && $errno = json_last_error()) {
           static::handleJsonError($errno);
       } elseif ($obj === null && $input !== 'null') {
           throw new DomainException('Null result with non-null input');
       }
       return $obj;
   }

   public static function jsonEncode($input)
   {
       $json = json_encode($input);
       if (function_exists('json_last_error') && $errno = json_last_error()) {
           throw new \Exception("json_last_error");
       } elseif ($json === 'null' && $input !== null) {
           throw new \Exception('Null result with non-null input');
       }
       return $json;
   }

   public static function urlsafeB64Decode($input)
   {
       $remainder = strlen($input) % 4;
       if ($remainder) {
           $padlen = 4 - $remainder;
           $input .= str_repeat('=', $padlen);
       }
       return base64_decode(strtr($input, '-_', '+/'));
   }

   public static function urlsafeB64Encode($input)
   {
       return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
   }

   private static function safeStrlen($str)
   {
       if (function_exists('mb_strlen')) {
           return mb_strlen($str, '8bit');
       }
       return strlen($str);
   } 
}