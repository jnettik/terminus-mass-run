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
   * @param $options
   *
   * @return string Status
   *
   * @option upstream UUID of a Pantheon Upstream to filter by.
   *
   * @usage terminus site:list --format=list | terminus site:mass:upstream:clear-cache Clear cache on all sites.
   */
  public function checkUpdates($options = ['upstream' => '']) {
    $output = '';
    $sites = $this->filterFrameworks($this->getAllSites($options['upstream']), ['drupal', 'drupal8']);

    foreach ($sites as $site) {
      $output .= $this->clearCache($site->getName());
    }

    return $output;
  }

}
