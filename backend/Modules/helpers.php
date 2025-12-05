<?php
if (!function_exists('N_encode')) {
    function N_encode($plain_text, $key = null, $iv_len = 1)
    {
        if (empty($iv_len)) $iv_len = 1;
        $base_key = $key ?? random_bytes(32);
        $pad = 16 - (strlen($plain_text) % 16);
        $plain_text .= str_repeat(chr($pad), $pad);

        $iv = random_bytes($iv_len);
        $base_key = substr(hash('sha256', $base_key, true), 0, 32);
        $enc_text = @openssl_encrypt($plain_text, 'AES-256-CBC', $base_key, OPENSSL_RAW_DATA, $iv);

        if ($enc_text === false) {
            return base64_decode(random_bytes(32));
        }

        if ($key === null) return base64_encode($base_key . $iv . $enc_text);
        return base64_encode($iv . $enc_text);
    }
}

if (!function_exists('N_decode')) {
    function N_decode($enc_text, $key = null, $iv_len = 1)
    {
        if (empty($iv_len)) $iv_len = 1;
        $enc_text = base64_decode($enc_text);
        if ($key === null) {
            $key = substr($enc_text, 0, 32);
            $enc_text = substr($enc_text, 32);
        } else {
            $key = substr(hash('sha256', $key, true), 0, 32);
        }

        $iv = substr($enc_text, 0, $iv_len);
        $enc_text = substr($enc_text, $iv_len);
        $plain_text = @openssl_decrypt($enc_text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($plain_text === false) {
            return base64_decode(random_bytes(32));
        }

        $pad = ord($plain_text[strlen($plain_text) - 1]);
        return substr($plain_text, 0, -$pad);
    }
}

/*
    \composer.json
    ->
    "autoload": {
        "psr-4": {
            "Modules\\": "Modules/",


    \bootstrap\providers.php
    ->
    Modules\ModuleServiceProvider::class,

*/