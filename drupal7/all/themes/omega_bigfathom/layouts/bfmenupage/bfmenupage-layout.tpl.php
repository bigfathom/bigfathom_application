<?php
$folderpath = dirname(__FILE__);
require_once("$folderpath/../standard_top_logic.php");
?>
<!-- Layout is bfmenupage -->
<div<?php print $attributes; ?>>
  <?php
    require_once("$folderpath/../standard_top_markup.php");
  ?>

  <div class="l-main">
    <div class="l-content" role="main">
      <?php print render($page['highlighted']); ?>
      <?php print $breadcrumb; ?>
      <a id="main-content"></a>
      <?php print render($title_prefix); ?>
      <?php if ($title): ?>
        <h1><?php print $title; ?></h1>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php print $messages; ?>
      <?php print render($tabs); ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
      <?php print render($page['content']); ?>
      <?php print $feed_icons; ?>
    </div>

    <?php //print render($page['sidebar_first']); ?>
    <?php //print render($page['sidebar_second']); ?>
  </div>

  <?php
    require_once("$folderpath/../standard_bottom_markup.php");
  ?>
</div>
