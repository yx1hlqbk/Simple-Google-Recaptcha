<?php

/**
 *
 */
class Recaptcha
{
    /**
     * 註冊位置
     */
    const sign_up_url = 'https://www.google.com/recaptcha/admin';

    /**
     * 驗證位置
     */
    const site_verify_url = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * API URI
     */
    const api_url = 'https://www.google.com/recaptcha/api.js';

    /**
     * 金鑰
     */
    protected $siteKey;

    /**
     * 密鑰
     */
    protected $secretKey;

    /**
     * 語言
     */
    protected $lang = 'en';

    /**
     * Recaptcha Construct
     */
    function __construct($config = [])
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }

        if (empty($this->siteKey) or empty($this->secretKey)) {
            $aTag = '<a href="'.self::sign_up_url.'">'.self::sign_up_url.'</a>';
            die("如果您要使用Google驗證機器人，請點擊該網站取得使用鑰匙$aTag");
        }
    }

    /**
     * Google 金鑰匙驗證 回覆
     *
     * @param String $response
     * @param String $remoteIp
     *
     * @return Array
     */
    public function verifyResponse($response, $remoteIp = null)
    {
        // Discard empty solution submissions
        if (empty($response)) {
            return [
                'success' => false,
                'error-codes' => 'missing-input',
            ];
        }

        $data = [
            'secret' => $this->secretKey,
            'response' => $response,
            // 'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        $result = $this->getResponseForGoogle($data);
        $responses = json_decode($result, true);

        if (isset($responses['success']) && $responses['success'] == true) {
            $status = true;
        } else {
            $status = false;
            $error = (isset($responses['error-codes'])) ? $responses['error-codes'] : 'invalid-input-response';
        }

        return [
            'success' => $status,
            'error-codes' => (isset($error)) ? $error : null,
        ];
    }

    /**
     * Google 驗證
     *
     * 取消file_get_contents用法 ， 改用CURL傳送
     * $response = file_get_contents($url);
     *
     * @param Array $data
     *
     * @return Json
     */
    private function getResponseForGoogle($data)
    {
        $url = self::site_verify_url.'?'.http_build_query($data);
        // $response = file_get_contents($url);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * Google js
     *
     * @param Array $parameters
     *
     * @return String
     */
    public function getScriptTag($parameters = [])
    {
        $default = array(
            'render' => 'onload',
            'hl' => $this->lang,
        );

        $result = array_merge($default, $parameters);

        $scripts = sprintf('<script type="text/javascript" src="%s?%s" async defer></script>',
            self::api_url, http_build_query($result));

        return $scripts;
    }

    /**
     * Google html tag
     *
     * @param Array $parameters
     *
     * @return String
     */
    public function getWidget($parameters = [])
    {
        $default = [
            'data-sitekey' => $this->siteKey,
            'data-theme' => 'light',
            'data-type' => 'image',
            'data-size' => 'normal',
        ];
        $result = array_merge($default, $parameters);
        $html = '';
        foreach ($result as $key => $value) {
            $html .= sprintf('%s="%s" ', $key, $value);
        }
        return '<div class="g-recaptcha" '.$html.'></div>';
    }
}
