<?php

namespace Pantheon\TerminusMassRun\Traits;

use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Models\Site;
use Pantheon\Terminus\Site\SiteAwareTrait;

trait TerminusMassRunTrait {

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
   * Filter sites by a provided Upstream UUID.
   *
   * @param array $sites
   *   An array of Pantheon Site objects.
   * @param string $upstream_uuid
   *   The UUID of the Upstream we want to target.
   *
   * @return array
   *   An array of Site objects matching the passed upstream.
   */
  protected function filterUpstream(array $sites, $upstream_uuid = '') {
    return array_filter($sites, function ($site) use ($upstream_uuid) {
      return $site->getUpstream()->serialize()['product_id'] == $upstream_uuid;
    });
  }

  /**
   * Filter sites by a provided array of frameworks. Defaults to Drupal sites.
   *
   * @param array $sites
   *   An array of Pantheon Site objects.
   * @param array $frameworks
   *   An array of framework IDs.
   *
   * @return array
   *   An array of Site objects matching the passed frameworks.
   */
  protected function filterFrameworks(array $sites, array $frameworks = ['drupal', 'drupal8']) {
    return array_filter($sites, function ($site) use ($frameworks) {
      return in_array($site->get('framework'), $frameworks);
    });
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
   * @param string $upstream
   *   A UUID of an upstream to filter by.
   *
   * @return array
   * @throws \Pantheon\Terminus\Exceptions\TerminusException
   */
  protected function getAllSites($upstream = '') {
    $sites = $this->readSitesFromSTDIN();

    if (empty($sites)) {
      throw new TerminusException('Input a list of sites by piping it to this command. Try running "terminus site:list | terminus {cmd}".', [
        'cmd' => $this->command,
      ]);
    }

    // Filter by upstreams if passed.
    if (!empty($upstream)) {
      $sites = $this->filterUpstream($sites, $upstream);
    }

    $this->log()->notice("{count} sites found.", ['count' => count($sites)]);

    return $sites;
  }

}
