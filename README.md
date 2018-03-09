# Terminus Mass Run

Terminus Mass Run takes a piped list of Pantheon sites runs a Terminus command on them in bulk. This takes the idea used in [Terminus Mass Update](https://github.com/pantheon-systems/terminus-mass-update) and expands on it for various terminus commands. Currently supported commands are:

* `terminus remote:drush`
* `terminus backup:create`
* `terminus env:deploy`
* `terminus connection:set`

Terminus provides several ways of getting a list of sites, the simplest being `terminus site:list --format=list`. The `--format=list` flat gets a list of site IDs that can be passed into Terminus Mass Run commands. For example, say you need to rebuild the cache on all production sites you have access too. Run:

```
$ terminus site:list --format=list | terminus remote:mass:drush -- cr
```
