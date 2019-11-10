<?php

namespace Drupal\usgo_wp_api_connect;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Render\FormattableMarkup;
# use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Client
 */
class Client {
    public static function getWpApiResponse($request_url,
                                      $request_query = array(),
                                      $base_url = "https://www.usgo.org") {
        $client = \Drupal::httpClient();

        try {
            if (empty($request_query)) {
                $client_response_main = $client->get($base_url . $request_url);
            } else { 
                $client_response_main = $client->get($base_url . $request_url, ['query' => $request_query]);
            }
    
            $result = $client_response_main->getBody()->getContents();

            return $result;
        } catch (ResponseException $error) {
            // Get the original response
            $response = $error->getResponse();
            // Get the info returned from the remote server.
            $response_info = $response->getBody()->getContents();
            // Using FormattableMarkup allows for the use of <pre/> tags, giving a more readable log item.
            $message = new FormattableMarkup('API ERROR: <pre>@response</pre>', ['@response' => print_r(json_decode($response_info), TRUE)]);
            // Log the error
            watchdog_exception('Remote API Connection', $error, $message);
        }
  }    
}
