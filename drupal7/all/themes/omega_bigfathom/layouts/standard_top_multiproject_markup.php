  <header class="l-header" role="banner">
    <div class="l-branding">
        <div class="heading-left">
            <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" class="site-logo"><img src="<?php print file_create_url('sites/all/themes/omega_bigfathom/images/bigfathom_arrows_logo_small.png'); ?>" alt="<?php print t('Home'); ?>" /></a>
            <h2 class="site-name">Bigfathom</h2>
        </div>  
        <div class="heading-right">
            <div class='banner-userinfo'>
                <?php if($user->uid !== 0): ?>
                <a title='<?php print "Currently logged in as ".$user->name;?>' href="<?php print url('user/logout'); ?>"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>
                <?php endif; ?>
            </div>
        </div>  
        <span class="heading-middle">
                <div class="heading-projectinfo-wrapper">
                    <div class="heading-pagetitle-wrapper">
                        <div class="heading-pagetitle" title='This is the current application context'>
                            <?php print $title; ?>
                        </div>
                    </div>
                </div>
        </span>  
      
    </div>
    <hr style="clear:both;">

    <?php print render($page['header']); ?>
    <?php //print render($page['navigation']); ?>
  </header>

