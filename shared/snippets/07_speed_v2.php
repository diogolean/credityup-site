// CY speed v2: gpt.js async + preconnects + wads.js defer (all front-end pages). Disable this snippet to revert.
add_action('template_redirect', function () {
  if (is_admin() || wp_doing_ajax()) return;
  ob_start(function ($h) {
    $h = str_replace('<script src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>',
      '<link rel="preconnect" href="https://api.winup.network" crossorigin><link rel="preconnect" href="https://erp.winup.network" crossorigin><link rel="preconnect" href="https://securepubads.g.doubleclick.net" crossorigin><script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>', $h);
    $h = preg_replace('#<script src="(https://api\.winup\.network/api/v1/public/kvp/wads\.js[^"]*)"></script>#', '<script defer src="$1"></script>', $h);
    return $h;
  });
});
