<?php
/**
 * RexSEO Addon
 *
 * @link http://gn2-code.de/projects/rexseo/
 * @link https://github.com/gn2netwerk/rexseo
 *
 * @author dh[at]gn2-netwerk[dot]de Dave Holloway
 * @author code[at]rexdev[dot]de jeandeluxe
 *
 * Based on url_rewrite Addon by
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4.2.x/4.3.x
 * @version 1.4
 * @version svn:$Id$
 */

// ADDON IDENTIFIER & ROOT DIR
////////////////////////////////////////////////////////////////////////////////
$myself = 'rexseo';
$myroot = $REX['INCLUDE_PATH'].'/addons/'.$myself.'/';

// LOCAL INCLUDES
////////////////////////////////////////////////////////////////////////////////
require_once $myroot.'/functions/function.rexseo_helpers.inc.php';

// HELP CONTENT
////////////////////////////////////////////////////////////////////////////////
$help_includes = array (
''            => array('Quickstart',                   'pages/help_quickstart.txt','textile'),
'settings'    => array('Einstellungen',                'pages/help_settings.txt','textile'),
'troubleshoot'=> array('Problemf&auml;lle & Sonstiges','pages/help_troubleshoot.txt','textile')
);



// OUTPUT
////////////////////////////////////////////////////////////////////////////////
foreach($help_includes as $key => $def)
{
  echo '
  <div class="rex-addon-output" style="overflow:auto">
    <h2 class="rex-hl2" style="font-size:1em">'.$def[0].' <span style="color: gray; font-style: normal; font-weight: normal;">( '.$def[1].' )</span></h2>
    <div class="rex-addon-content">
      <div class="rexseo">
        '.rexseo_incparse($myroot,$def[1],$def[2],true).'
      </div>
    </div>
  </div>';
}
?>