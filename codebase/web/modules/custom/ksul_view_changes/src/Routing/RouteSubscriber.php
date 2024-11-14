<?php

namespace Drupal\ksul_view_changes\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {

    // check for those pesky fgsc duplicate title records and clean the url_alias
    $pattern = '/(fgsc-[a-zA-Z0-9]+)-[0-9]$/';
    foreach ($collection as $route_name => $route) {
      $path = $route->getPath();

      // Check if the path matches the pattern
      if (preg_match($pattern, $path)) {
        // Remove the trailing '-0' or '-[number]' if it matches the pattern
        $new_path = preg_replace($pattern, '$1', $path);

        // Set the updated path
        $route->setPath($new_path);
      }
    }
    // Override the login path to point to /caslogin.
    if ($route = $collection->get('user.login')) {
      $route->setPath('/caslogin');
    }
  }
}
