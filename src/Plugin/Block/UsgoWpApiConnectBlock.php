<?php

namespace Drupal\usgo_wp_api_connect\Plugin\Block;

use Drupal\usgo_wp_api_connect\Client;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockBase;
use Drupal\Component\Render\FormattableMarkup;

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
    $client_response_contents = Json::decode(Client::getWpApiResponse('/news/wp-json/wp/v2/posts', [
        'categories' => 712,
        'per_page' => 5,
        'category_exclude' => 1182,
    ]));

    $block_content = '';

    foreach ($client_response_contents as $client_response_content) {
        $block_content .= '<a href="' . $client_response_content['link'] . '">';
        $block_content .= '<h2 class="storytitle">' . $client_response_content['title']['rendered'] . '</h2>';
        $block_content .= '</a>';
        $block_content .= $client_response_content['excerpt']['rendered'];
   }

    $build = array(
       '#markup' => $this->t($block_content)
    );
    
    return $build;
  }
}
