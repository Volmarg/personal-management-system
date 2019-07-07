<?php

namespace App\Controller\Curl;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CurlController
 * @package App\Controller\Curl
 * This class was generally used with GoogleApi, that I've removed from here in the end
 * However this class is being left as maybe curl will be reused at some point in future.
 */

class CurlController extends AbstractController {

    public function fetchData(string $url, $authenticate = false, $custom_request_type = false, array $data = []) {
        $ch = curl_init();
        $headers = $authentication_headers = ($authenticate == false ? [] : $this->makeAuthenticationHeaders());

        if ($custom_request_type && !empty($data)) {
            $data_headers = $this->makeJsonDataHeaders($data);
            $headers = array_merge($authentication_headers, $data_headers['content_length']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_request_type);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_headers['data_json']);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


        $result = curl_exec($ch);

        return $result;

    }

    private function makeAuthenticationHeaders() {
        return [
            'Authorization: Bearer ' . Config::getGoogleApiAccessToken(false),
            'Accept: application/json'
        ];
    }

    private function makeJsonDataHeaders(array $data): array {
        $data_json = json_encode($data);
        return [
            'content_length' => ["Content-Type: application/json','Content-Length:" . strlen($data_json)],
            'data_json' => $data_json
        ];

    }
}