<?php
global $user;
if($user->uid == 0)
{
    if(!isset($_GET['redirectedforlogin']))
    {
        $curpath = current_path();
        if(0 !== strpos($curpath,'bigfathom/public'))
        {
            //Redirect to the login page!
            drupal_goto('user/login', array('query'=>array('redirectedforlogin'=>1)));
        }
    }
}

//$loadedtest = module_load_include('php','bigfathom_core','core/Context');
if(module_exists('bigfathom_core'))
{
    //Okay, we can run these things
    $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('dashboard');
    $menu_key = $info_bundle['menu_key'];

    $links_ar = [];
    $all_bundles = \bigfathom\UtilityGeneralFormulas::getAllUIPageBundles();
    foreach($all_bundles as $info_bundle)
    {
        $menu_key = $info_bundle['menu_key'];
        $label = $info_bundle['label'];
        $font_awesome_class = $info_bundle['font_awesome_class'];
        $href = url($menu_key, array('query'=>array('jumpfrom'=>'topicon')));
        $iconmarkup = "<a title='Jump to the $label options' href='$href'><i class='$font_awesome_class'></i></a>";
        $links_ar[] = $iconmarkup;
    }
    $top_jumps_markup = implode(" ", $links_ar);
} else {
    $top_jumps_markup = "";
}

//<?php 
function get_browser_name($user_agent)
{
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
    elseif (strpos($user_agent, 'Edge')) return 'Edge';
    elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
    elseif (strpos($user_agent, 'Safari')) return 'Safari';
    elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
   
    return 'Other';
}
$bn = get_browser_name($_SERVER['HTTP_USER_AGENT']);
if(strtolower($bn) != 'firefox')
{
    print "<h1 style='color:red;background-color:yellow;align=center;'>WARNING YOU ARE NOT USING THE FIREFOX BROWSER SO SOME FEATURES WILL NOT WORK! (Detected $bn)"
            . "</h1><hr>";
}
//>