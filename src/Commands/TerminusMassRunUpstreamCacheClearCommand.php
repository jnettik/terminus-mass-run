<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Site\Upstream\ClearCacheCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunUpstreamCacheClearCommand extends ClearCacheCommand implements SiteAwareInterface {

  use TerminusMassRunTrait;

  /**
   * Get an ouput of site data.
   *
   * @authorize
   *
   * @command site:mass:upstream:clear-cache
   * @aliases mass-upstream-check
   *
   * @return string Status
   *
   * @usage terminus site:list --format=list | terminus site:mass:upstream:clear-cache Clear cache on all sites.
   */
  public function checkUpdates() {
    $output = '';
    $sites = array_filter($this->getAllSites(), function ($site) {
      // Check it's a Drupal site.
      return in_array($site->get('framework'), ['drupal', 'drupal8']);
    });

    foreach ($sites as $site) {
      $output .= $this->clearCache($site->getName());
    }

    return $output;
  }

}
