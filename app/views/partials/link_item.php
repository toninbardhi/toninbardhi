<?php
/** Elemento di un link nelle liste pubbliche. Variabili: $l, $showCategory. */
$showCategory = $showCategory ?? false;
$feat  = !empty($l['featured']);
$thumb = thumb_url($l);
?>
<li class="<?= $feat ? 'is-featured' : '' ?>">
  <div class="link-row">
    <?php if ($thumb !== ''): ?>
      <img class="link-thumb" src="<?= e($thumb) ?>" alt="" loading="lazy" onerror="this.style.display='none'">
    <?php endif; ?>
    <div class="link-body">
      <div class="title">
        <a href="<?= e($l['url']) ?>" target="_blank" rel="nofollow noopener <?= $feat ? 'sponsored' : '' ?>"><?= e($l['title']) ?></a>
        <?php if ($feat): ?><span class="badge featured">Sponsorizzato</span><?php endif; ?>
      </div>
      <div class="url"><?= e($l['url']) ?></div>
      <?php if (!empty($l['description'])): ?>
        <div class="desc"><?= e($l['description']) ?></div>
      <?php endif; ?>
      <?php if ($showCategory && !empty($l['category_path'])): ?>
        <div class="cat">in <a href="<?= e(url($l['category_path'])) ?>"><?= e($l['category_name']) ?></a></div>
      <?php endif; ?>
      <?php if (has_map($l)): ?>
        <?php $lat = (float) $l['latitude']; $lng = (float) $l['longitude']; ?>
        <div class="link-map">
          <button type="button" class="map-toggle" data-embed="<?= e(osm_embed_url($lat, $lng)) ?>">📍 Mostra mappa</button>
          <?php if (!empty($l['address'])): ?>
            <span class="map-addr"><?= e($l['address']) ?></span>
          <?php endif; ?>
          <div class="map-holder"></div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</li>
