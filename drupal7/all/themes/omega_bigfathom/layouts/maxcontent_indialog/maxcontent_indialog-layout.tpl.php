<?php
$folderpath = dirname(__FILE__);
require_once("$folderpath/../standard_top_logic.php");
?>
<!-- Layout is maxcontent_multiproject -->
<div<?php print $attributes; ?>>

  <div class="l-main">
    <div class="l-content" role="main">
      <?php print $messages; ?>
      <?php print render($page['content']); ?>
    </div>
  </div>
</div>
