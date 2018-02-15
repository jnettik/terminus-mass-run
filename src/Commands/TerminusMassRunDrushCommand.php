<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Remote\DrushCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunDrushCommand extends DrushCommand implements SiteAwareInterface {

  use TerminusMassRunTrait;

  /**
   * Get an ouput of site data.
   *
   * @authorize
   *
   * @command remote:mass:drush
   * @aliases mass-drush
   *
   * @param array $cmd
   * @param array $options
   * @return string Command output
   *
   * @option env The Pantheon environments to target. Defaults to `live`.
   */
  public function runCommand(array $cmd, $options = ['env' => 'live']) {
    $sites = $this->getAllSites();
    $output = '';

    foreach ($sites as $site) {
      $output .= $this->drushCommand("{$site->getName()}.{$options['env']}", $cmd);
    }

    return $output;
  }

}
