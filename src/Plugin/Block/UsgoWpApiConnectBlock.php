<?php

namespace Drupal\usgo_wp_api_connect\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockBase;
use Drupal\Component\Render\FormattableMarkup;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides a USGO WP API Connect Block
 *
 * @Block(
 *   id = "usgo_wp_api_connect_block",
 *   admin_label = @Translation("USGO WP API Connect Block"),
 * )
 */
class UsgoWpApiConnectBlock extends BlockBase {

  /**
   * {@inheritdoc}
   *
   * The return value of the build() method is a renderable array. Returning an
   * empty array will result in empty block contents. The front end will not
   * display empty blocks.
   */
  public function build() {
    $client_response_contents = Json::decode($this->getWpApiResponse('/news/wp-json/wp/v2/posts', [
        'categories' => 712,
        'per_page' => 5,
        'category_exclude' => 1182,
    ]));

    $frontpage_content = '';

    foreach ($client_response_contents as $client_response_content) {
        $frontpage_content .= '<a href="' . $client_response_content['link'] . '">';
        $frontpage_content .= '<h2 class="storytitle">' . $client_response_content['title']['rendered'] . '</h2>';
        $frontpage_content .= '</a>';
        $frontpage_content .= $client_response_content['content']['rendered'];

        $content_categories = '';
        $content_categories .= '<div class="categories">';
        $content_categories .= 'Categories: ';

        $content_category_links = array();

        foreach ($client_response_content['categories'] as $category) {
            $content_category_response = $this->getWpApiResponse("/news/wp-json/wp/v2/categories/$category");
            $content_category_response_decoded = Json::decode($content_category_response);

            $content_category_link = '<a href="' . $content_category_response_decoded['link'] . '">';
            $content_category_link .= $content_category_response_decoded['name'];
            $content_category_link .= '</a>';

            array_push($content_category_links, $content_category_link);
        }
        $content_categories .= rtrim(implode(', ', $content_category_links));

        $content_categories .= '</div>';

        $frontpage_content .= $content_categories;
    }

    $build = array(
       '#markup' => $this->t($frontpage_content)
    );
    
    return $build;
  }

  protected function getWpApiResponse($request_url,
                                      $request_query = array(),
                                      $base_url = "https://www.usgo.org") {
        $client = \Drupal::httpClient();

        try {
            if (empty($request_query)) {
                $client_response_main = $client->get($base_url . $request_url);
            } else { 
                $client_response_main = $client->get($base_url . $request_url, ['query' => $request_query]);
            }
            
            $result = $client_response_main->getBody();
            
            return $result;
        } catch (GuzzleException $error) {
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
