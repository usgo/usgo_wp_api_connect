<?php

namespace Drupal\usgo_wp_api_connect\Controller;

use Drupal\usgo_wp_api_connect\Client as UsgoWpApiClient;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller routines for page example routes.
 */
class UsgoWpApiConnectController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'usgo_wp_api_connect';
  }

  /**
   * Constructs a simple frontpage using the wordpress api for the usgo association
   *
   */
  public function frontpage() {
    $client_response_contents = Json::decode(UsgoWpApiClient::getWpApiResponse('/news/wp-json/wp/v2/posts', [
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
            $content_category_response = UsgoWpApiClient::getWpApiResponse("/news/wp-json/wp/v2/categories/$category/");
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

    $content = array(
       '#markup' => $this->t($frontpage_content)
    );

    return $content;
  }
}
