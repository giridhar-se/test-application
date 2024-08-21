<?php

use Drupal\node\Entity\Node;
use Drupal\media\Entity\Media;
use Drush\Drush;


function drush_log($msg, $lvl) {
  switch ($lvl) {
    case 'error':
      Drush::logger()->error($msg);
      break;
    
    default:
    Drush::logger()->notice($msg);
      break;
  }
}

/**
 * Deletes media entities associated with a node.
 */
function delete_media_by_node($nid = null) {
  // Query for media items that reference the node.
  $media_ids = \Drupal::entityQuery('media')
    ->condition('field_media_of', $nid)
    ->accessCheck(FALSE)
    ->execute();

  $total_media = count($media_ids);
  // Load and delete each media item.
  foreach ($media_ids as $media_id) {
    $media_item = Media::load($media_id);
    if ($media_item) {
      $media_item->delete();
    }
  }
  if($nid == null) {
    drush_log($total_media." Orphaned Media deleted","notice");
  } else {
    drush_log($total_media." Media deleted","notice");
  }
}

/**
 * Deletes a node and its associated children and media.
 */
function delete_node_with_children($nid) {
  // Load the parent node.
  $node = Node::load($nid);
  if (!$node) {
    drush_log("Node with ID $nid not found.", 'error');
    return;
  }

  // Log the deletion process.
  drush_log("Deleting node with ID $nid and its children.", 'notice');

  // Load the child nodes.
  $child_nids = \Drupal::entityQuery('node')
    ->condition('field_member_of', $nid)
    ->accessCheck(FALSE)
    ->execute();

  $progress = 0;
  $total_children = count($child_nids);
  // Delete child nodes and their associated media.
  foreach ($child_nids as $child_nid) {
    $child_node = Node::load($child_nid);
    if ($child_node) {
      drush_log("Deleting child node ".$child_nid. " - ".$progress."/".$total_children, 'notice');
      delete_media_by_node($child_nid);
      $child_node->delete();
      $progress++;
    }
  }

  // Delete media associated with the parent node.
  delete_media_by_node($nid);

  // Delete the parent node.
  $node->delete();
  drush_log("Node with ID $nid and its children have been deleted.", 'notice');
}

// Get the node ID from the arguments passed to the script.
if (isset($extra[0])) {
  $nid = $extra[0];
  delete_node_with_children($nid);
} else {
  drush_log("Please provide a node ID as an argument.", 'error');
  drush_log("argv = ".$extra[0],'notice');
}
