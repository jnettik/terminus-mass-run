<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Models\Site;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Commands\Remote\DrushCommand;

class TerminusMassRunDrushCommand extends DrushCommand implements SiteAwareInterface {

  use SiteAwareTrait;

  /**
   * Additional site validation.
   *
   * @param \Pantheon\Terminus\Models\Site $site
   *   A Pantheon site instance.
   *
   * @return \Pantheon\Terminus\Models\Site
   *   Returns the Site object.
   * @throws \Pantheon\Terminus\Exceptions\TerminusException
   */
  protected function validateSite(Site $site) {
    if ($site->isFrozen()) {
      throw new TerminusException('{site_name} is frozen.', [
        'site_name' => $site->getName(),
      ]);
    }

    return $site;
  }

  /**
   * Read a list of site ids passed through STDIN and load the sites.
   *
   * @return array
   */
  protected function readSitesFromStdin() {
    // If STDIN is interactive then nothing was piped to the command. We don't
    // want to hang forever waiting for input as this is not meant to be
    // interactive.
    if (posix_isatty(STDIN)) { return []; }
    $sites = [];

    while ($line = trim(fgets(STDIN))) {
      try {
        $sites[] = $this->validateSite($this->sites->get($line));
      }
      catch (TerminusException $e) {
        // If the line isn't a valid site id, ignore it.
        continue;
      }
    }

    return $sites;
  }

  /**
   * Get a list of the sites and updates with the given options.
   *
   * @return array
   * @throws \Pantheon\Terminus\Exceptions\TerminusException
   */
  protected function getAllSites() {
    $sites = $this->readSitesFromSTDIN();

    if (empty($sites)) {
      throw new TerminusException('Input a list of sites by piping it to this command. Try running "terminus site:list | terminus {cmd}".', [
        'cmd' => $this->command,
      ]);
    }

    $this->log()->notice("{count} sites found.", ['count' => count($sites)]);

    return $sites;
  }

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
