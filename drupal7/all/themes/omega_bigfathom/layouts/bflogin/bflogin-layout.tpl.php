<?php
global $user;
if($user->uid == 0)
{
    if(!isset($_GET['redirectedforlogin']))
    {
        $curpath = current_path();
        if(0 !== strpos($curpath,'user/') && 0 !== strpos($curpath,'bigfathom/public'))
        {
            //Redirect to the login page!
            $loginurl = url('user/login');
            $urlencoded = urlencode($curpath);
            drupal_goto('user/login', array('query'=>array('redirectedforlogin'=>1,'originalpath'=>$urlencoded)));
        }
    }
}
$folderpath = dirname(__FILE__);
require_once("$folderpath/../standard_top_logic.php");
?>
<!-- Layout is bflogin -->
<div<?php print $attributes; ?>>
  <header class="l-header" role="banner">
    <div class="l-branding">
      <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" class="site-logo"><img src="<?php print file_create_url('sites/all/themes/omega_bigfathom/images/bigfathom_arrows_logo_small.png'); ?>" alt="<?php print t('Home'); ?>" /></a>
      <h2 class="site-name">Bigfathom</h2>
      <?php if (isset($_SESSION['selected_projectid'])): ?>
      <h2 class='selected-project-title'>Selected Project:<span class='selected-project-title-text'> <?php print $_SESSION['selected_root_workitem_nm']; ?></span></h2>
      <?php endif; ?>
      <?php print render($page['branding']); ?>
    </div>
    <div class='banner-userinfo'>
        <?php if($user->uid !== 0): ?>
        <a title='<?php print "Currently logged in as ".$user->name;?>' href="<?php print url('user/logout'); ?>"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
        <?php endif; ?>
    </div>
    <hr style="clear:both;">

    <?php print render($page['header']); ?>

  </header>

  <div class="l-main">
    <div class="l-content" role="main">
        <div style='margin: auto; display: inline-block;'>
            <?php print render($page['highlighted']); ?>
            <a id="main-content"></a>
            <?php print $messages; ?>
            <?php print render($page['help']); ?>
            <?php print render($page['content']); ?>
        </div>
    </div>
  </div>

  <?php
    require_once("$folderpath/../standard_bottom_markup.php");
  ?>
</div>
