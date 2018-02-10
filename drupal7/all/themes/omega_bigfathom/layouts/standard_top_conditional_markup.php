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
            <?php if (isset($_SESSION['selected_projectid'])): ?>
                <div class="heading-projectinfo-wrapper">
                    <div class="heading-projectname-wrapper">
                        <div class="heading-projectname" title='This is the name of the currently selected project'>
                            <?php print $_SESSION['selected_root_workitem_nm']; ?>
                        </div>
                    </div>
                    <div class="heading-projectpurpose-wrapper">
                        <?php if (!empty($_SESSION['selected_root_purpose_tx4heading'])): ?>
                            <div class="heading-projectpurpose" title="Declared purpose of this project">
                                <?php print $_SESSION['selected_root_purpose_tx4heading']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="heading-projectinfo-wrapper">
                    <div class="heading-pagetitle-wrapper">
                        <div class="heading-pagetitle" title='No project is currently selected'>
                            <?php print $title; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </span>  
    </div>
    <hr style="clear:both;">

    <?php print render($page['header']); ?>
    <?php //print render($page['navigation']); ?>
    
  </header>

