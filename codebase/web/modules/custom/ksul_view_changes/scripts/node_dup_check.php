<?php

use Drupal\Core\Database\Database;
use Drush\Drush;

/**
 * Checks for nodes with duplicate titles.
 */
function check_duplicate_titles() {
  $database = Database::getConnection();
  $query = $database->select('node_field_data', 'nfd')
    ->fields('nfd', ['title'])
    ->condition('status', 1) // Only published nodes
    ->groupBy('title')
    ->having('COUNT(nfd.title) > 1')
    ->execute();

  foreach ($query as $record) {
    $duplicate_title = $record->title;

    // Get all node IDs with this title.
    $nid_query = $database->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('title', $duplicate_title)
      ->execute();

    $node_ids = [];
    foreach ($nid_query as $nid_record) {
      $node_ids[] = $nid_record->nid;
    }

    Drush::logger()->notice("Duplicate title found: '$duplicate_title' in nodes: " . implode(', ', $node_ids));
  }
}

// Run the function.
check_duplicate_titles();
