<?php

class ImageConvert
{
    const DEVICE_TYPE_FOR_IPHONE4       = 0;
    const DEVICE_TYPE_FOR_IPHONE5       = 1;
    const DEVICE_TYPE_FOR_ANDROID       = 2;
    const DEVICE_TYPE_FOR_WINDOWS_PHONE = 3;
    const DEVICE_TYPE_FOR_IPAD          = 4;
    const DEVICE_TYPE_FOR_ANDROID_PAD   = 5;
    const DEVICE_TYPE_FOR_DESKTOP       = 6;

    private $apikey = '';
    private $apiUrl = 'http://api.page2images.com/restfullink';

    private $url       = '';
    private $device    = self::DEVICE_TYPE_FOR_DESKTOP;
    private $timeout   = 120;

    private $imageUrl  = '';
    private $error     = '';

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setDevice($device)
    {
        $this->device = $device;
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 画像変換APIにアクセスした結果を返却する
     *
     *
     */
    public function connect(array $parameters)
    {
        if (!count($parameters))
        {
            return false;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * HTMLを画像に変換する
     *
     *
     */
    public function execute()
    {
        $result = false;

        try
        {
            $result = $this->__execute();
        }
        catch (Exception $e)
        {
            $this->error = 'Caught exception: ' . $e->getMessage();
        }

        return $result;
    }

    private function __execute()
    {
        $startTime  = time();

        $parameters = array(
            'p2i_url'     => $this->url,
            'p2i_key'     => $this->apikey,
            'p2i_device'  => $this->device,
            'p2i_refresh' => 1,
        );

        while (true)
        {
            $response = $this->connect($parameters);

            if (empty($response))
            {
                $this->error = 'something error';
                return false;
            }
            else
            {
                $json_data = json_decode($response);

                if (empty($json_data->status))
                {
                    return false;
                }
            }

            switch ($json_data->status)
            {
                case 'error':
                    $this->error = $json_data->errno . ' ' . $json_data->msg;
                    return false;

                case 'finished':
                    $this->imageUrl = $json_data->image_url;
                    return true;

                case 'processing':
                default:
                    if ((time() - $startTime) > $this->timeout)
                    {
                        $this->error = 'Error: Timeout after ' . $this->timeout . ' seconds.';
                        return false;
                    }

                    sleep(3);
                    break;
            }
        }
    }
}