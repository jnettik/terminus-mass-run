<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Connection\SetCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunConnectionSetCommand extends SetCommand implements SiteAwareInterface {

  use TerminusMassRunTrait;

  /**
   * Mass setting of site connection mode.
   *
   * @authorize
   *
   * @command connection:mass:set
   * @aliases mass-connection-set
   *
   * @param string $mode [git|sftp] Connection mode
   * @param string $site_env Environment to target. Defaults to `dev`. Excludes `test` and `live`.
   * @return string Command output
   *
   * @usage terminus site:list --format=list | terminus env:mass:deploy --env=<env> --note=<note>
   */
  public function massConnectionSet($mode, $site_env = 'dev') {
    $output = '';

    $sites = array_filter($this->getAllSites(), function ($site) {
      // Check it's a Drupal site.
      return in_array($site->get('framework'), ['drupal', 'drupal8']);
    });

    foreach ($sites as $site) {
      try {
        $output .= $this->connectionSet("{$site->getName()}.{$site_env}", $mode);
      }
      catch (TerminusException $e) {
        // If the command doesn't run, we want to skip it and continue to run
        // the rest of the scripts.
        $this->log()->error('Connection not set for for {site_name}.{env}.', [
          'site_name' => $site->getName(),
          'env' => $options['env'],
        ]);
        continue;
      }
    }

    return $output;
  }

}
