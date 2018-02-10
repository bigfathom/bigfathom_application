/* THIS HOOK IS NOT WORKING???????????????
function omega_bigfathom_omega_menu_link(&$variables)
{
    $element = &$variables['element'];
    error_log("LOOK XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX menu thing element=" . print_r($element,TRUE));
 
    throw new \Exception("LOOK STOP NOW");
    
    return theme_menu_link($variables);
}
*/