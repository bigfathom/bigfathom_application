<?php
$folderpath = dirname(__FILE__);
require_once("$folderpath/../standard_top_logic.php");
?>
<!-- Layout is bftopmenu_multiproject -->
<div<?php print $attributes; ?>>
  <?php
    require_once("$folderpath/../standard_top_multiproject_markup.php");
  ?>

  <div class="l-main">
    <div class="l-content" role="main">
      <?php print render($page['highlighted']); ?>
      <a id="main-content"></a>
      <?php print $messages; ?>
      <?php print render($tabs); ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
      <?php print render($page['content']); ?>
      <?php print $feed_icons; ?>
    </div>
  </div>

  <?php
    require_once("$folderpath/../standard_bottom_markup.php");
  ?>
</div>
