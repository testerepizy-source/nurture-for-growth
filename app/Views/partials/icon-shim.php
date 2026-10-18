<?php
// Small runtime shim that exposes a map of svg icons (from svg_icon helper)
// and replaces <i class="fas fa-..."> nodes with inline SVGs on DOMContentLoaded.
// This is a non-destructive way to phase out Font Awesome immediately for the public UI.

require_once __DIR__ . '/icons.php';

$names = [
  'user','calendar','eye','phone','search','book-open','thumbtack','menu','globe',
  'hand-heart','shield','calendar-check','user-group','check-circle','chart','plus','edit','trash','download','upload','cog','times','arrow-right'
];

$map = [];
foreach ($names as $n) {
  $map[$n] = svg_icon($n, 18, 'class="inline-svg"');
}

// JSON-safe: escape closing tags to avoid breaking out of script
$jsMap = [];
foreach ($map as $k => $v) {
  $vEsc = str_replace('</', '<\\/', $v);
  $jsMap[$k] = $vEsc;
}
?>
<script>
(function(){
  window.__SVG_ICON_MAP = <?php echo json_encode($jsMap, JSON_UNESCAPED_SLASHES); ?>;
  document.addEventListener('DOMContentLoaded', function(){
    try {
      var nodes = document.querySelectorAll('i[class*="fa-"]');
      nodes.forEach(function(i){
        var cls = Array.from(i.classList).find(function(c){ return c.indexOf('fa-') === 0 || c.indexOf('fa-')>0 && c.includes('fa-'); });
        if(!cls) return;
        // extract name after 'fa-'
        var match = cls.match(/fa-([a-z0-9-]+)/i);
        if(!match) return;
        var name = match[1];
        var svg = window.__SVG_ICON_MAP[name];
        if(svg) {
          var wrapper = document.createElement('span');
          wrapper.className = 'icon-replaced';
          wrapper.innerHTML = svg;
          i.parentNode.replaceChild(wrapper, i);
        }
      });
    }catch(e){ console.error(e); }
  });
})();
</script>
<style>
.inline-svg { width: 1.1em; height: 1.1em; vertical-align: -0.125em; }
.icon-replaced svg { display: inline-block; vertical-align: middle; }
</style>