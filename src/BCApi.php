<?php


namespace src;

/**
 * Надсилає запити для взаємодії з API сайту
 */
class BCApi
{
    public function remove_utf8_bom($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    public function sendRequest(array $post)
    {
        $curl = curl_init();
        $array = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => API_URL,
            CURLOPT_USERAGENT      => 'BCAgent',
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_HTTPHEADER     => ['Cache-Control: no-cache'],
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_HEADER         => 0,
            CURLOPT_FOLLOWLOCATION => true,
        ];

        curl_setopt_array($curl, $array);
        $resp = curl_exec($curl);

        curl_close($curl);
        $resp = $this->remove_utf8_bom($resp);

        $result = json_decode($resp, true);

        return $result;
    }
}
