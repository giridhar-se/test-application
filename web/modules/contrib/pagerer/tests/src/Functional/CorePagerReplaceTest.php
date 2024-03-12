<?php

namespace Drupal\Tests\pagerer\Functional;

use Drupal\Tests\system\Functional\Pager\PagerTest;
use Drupal\user\Entity\Role;

/**
 * Test replacement of Drupal core pager.
 *
 * @group Pagerer
 */
class CorePagerReplaceTest extends PagerTest {

  /**
   * The URL for Pagerer admin UI page.
   *
   * @var string
   */
  protected string $pagererAdmin = 'admin/config/user-interface/pagerer';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dblog',
    'image',
    'pager_test',
    'pagerer',
    'pagerer_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Grant new permission for admin user role.
    $roles = $this->adminUser->getRoles(TRUE);
    Role::load(reset($roles))
      ->grantPermission('administer site configuration')
      ->save();

    // Replace the core pager.
    $this->drupalGet($this->pagererAdmin . '/preset/add');
    $this->submitForm(['label' => 'core_replace', 'id' => 'core_replace'], 'Create');
    $this->drupalGet($this->pagererAdmin);
    $this->submitForm(['core_override_preset' => 'core_replace'], 'Save configuration');
  }

  /**
   * Test that pagerer specific cache tags have been added.
   */
  public function testPagerQueryParametersAndCacheContext(): void {
    parent::testPagerQueryParametersAndCacheContext();
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'config:pagerer.settings');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'config:pagerer.preset.core_replace');
  }

}
