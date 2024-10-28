<?php

namespace Drupal\ksul_view_changes\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    // Override the login path to point to /caslogin.
    if ($route = $collection->get('user.login')) {
      $route->setPath('/caslogin');
    }
  }
}
