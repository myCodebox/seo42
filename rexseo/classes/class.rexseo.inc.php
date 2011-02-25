<?php

/**
 * REXseo
 * Based on the URL-Rewrite Addon
 * @author dh[at]gn2-netwerk[dot]de Dave Holloway
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author code[at]rexdev[dot]de jeandeluxe
 * @package redaxo4.2
 * @version 1.2
 * @version svn:$Id$
 */


class rexseo {


  function title($artID=null)
  {
    global $REX;
    $artID=intval($artID);
    if (!$artID)
    {
      $artID=$REX['ARTICLE_ID'];
    }

    $curart = OOArticle::getArticleById($artID);
    $parents = $curart->getParentTree();

    if ($curart->getValue('name') != $curart->getValue('catname'))
    {
      array_push($parents, $curart);
    }

    if (empty($parents))
    {
      $parents[0]=$curart;
    }
    else
    {
      $parents = array_reverse($parents);
    }

    // BREADCRUMB TITLE
    $B = '';
    foreach ($parents as $parent)
    {
      if (OOArticle::isValid($parent))
      {
        $B .= ' - '.$parent->getValue('name');
      }
      elseif (OOCategory::isValid($parent))
      {
        $B .= ' - '.$parent->getValue('catname');
      }
    }
    $B = trim($B);
    $B = trim($B,"-");
    $B = trim($B);

    // SIMPLE TITLE
    $N = $this->getValue('name');

    // SERVERNAME
    $S = $REX['SERVERNAME']!='' ? $REX['SERVERNAME'] : $_SERVER['HTTP_HOST'] ;

    // OVERRIDE: REXSEO TITLE
    if($this->getValue('art_rexseo_title')!='')
    {
      $B = $N = $this->getValue('art_rexseo_title');
    }
    
    $title = str_replace(array('%B','%N','%S'),array($B,$N,$S),$REX['ADDON']['rexseo']['settings']['title_schema']);

    $title = rexseo::htmlentities($title);

    return $title;
  }


  function keywords($artID=null) {
    global $REX;
    $artID=intval($artID); /* ONLY INTEGERS */
    if (!$artID) { $artID=$REX['ARTICLE_ID']; }


    $x = OOArticle::getArticleById($REX['START_ARTICLE_ID']);
    $keys = self::getMetaField($artID,"art_keywords",$x->getValue('art_keywords'),'LOOP');

    if ($keys=='') {
      $keys = $REX['ADDON']['rexseo']['settings']['def_keys'][$REX['CUR_CLANG']];
    }

    $keys = str_replace("\r\n",' ',$keys);
    $keys = str_replace("\n",' ',$keys);
    $keys = str_replace("\r",' ',$keys);

    $keys = explode(',',$keys);
    $str = '';
    foreach ($keys as $key) {
      $key = trim($key);
      if ($key!="") {
        $str .= $key.',';
      }
    }
    $str = trim($str,',');
    $str = rexseo::htmlentities($str);
    return $str;
  }


  function description($artID=null) {
    global $REX;
    $artID=intval($artID); /* ONLY INTEGERS */
    if (!$artID) { $artID=$REX['ARTICLE_ID']; }


    $x = OOArticle::getArticleById($REX['START_ARTICLE_ID']);
    $desc = self::getMetaField($artID,"art_description",$x->getValue('art_description'),'LOOP');

    if ($desc=='') {
      $desc = $REX['ADDON']['rexseo']['settings']['def_desc'][$REX['CUR_CLANG']];
    }

    $desc = str_replace("\r\n",' ',$desc);
    $desc = str_replace("\n",' ',$desc);
    $desc = str_replace("\r",' ',$desc);

    $str = trim($desc);
    $str = rexseo::htmlentities($str);

    return $str;
  }


  function canonical($artID=null) {
    global $REX;

    if (!$artID) {
      $artID=$REX['ARTICLE_ID'];
    }
    else {
      $artID=intval($artID);
    }

    $canonical = self::getMetaField($artID,'art_rexseo_canonicalurl',rex_getURL($artID,$REX['CUR_CLANG']));
    $canonical = $REX['SERVER'].ltrim($canonical,'/');

    return $canonical;
  }

  function islatin() {
    global $REX;
    $pos = strpos($REX['LANG'], '_utf8');
    if ($pos === false) {
      return true;
    } else {
      return false;
    }
  }

  function htmlentities($str) {
    if (rexseo::islatin()) {
      return htmlentities($str,ENT_QUOTES);
    } else {
      return htmlentities($str,ENT_QUOTES,'UTF-8');
    }
  }

  function getHost() {
    global $REX;
    if ($REX['ADDON']['rexseo']['settings']['enable_multidomain']==1) 
    {
      $server = $_SERVER['SERVER_NAME'];
    } else {
      $server = $REX['SERVER'];
    }
    if ($server == '') {
      $server = $_SERVER['HTTP_HOST'];
    }
    if ($server != '') {
      return $server;
    }
  }

  function base() {
    global $REX;
    
    if ($REX['ADDON']['rexseo']['settings']['enable_multidomain']==1)
    { $server = self::getHost();
      if ($server!='')
      { 
        return 'http://'.$server; //TODO: detect ssl
      }
    }
    return $REX["SERVER"];
  }

  function getMultidomainSettings() {
    global $REX;
    $found = false;
    $server = rexseo::getHost();
    if (isset($REX['ADDON']['rexseo']['settings']['multidomain']))
    {
      if (is_array($REX['ADDON']['rexseo']['settings']['multidomain'])) 
      {
        foreach ($REX['ADDON']['rexseo']['settings']['multidomain'] as $k=>$entry) 
        { if ($found) break;
          if ($k==$server) {
            return $entry;
          }
        }
      }
    }
    
    return false;
  }

  function startMultidomain() {
    
    $found = rexseo::getMultidomainSettings();
    
    if (isset($found['article_id']) && isset($found['clang']))
    { $REX['SERVER'] = rexseo::base();
      
      
      $REX['START_ARTICLE_ID'] = $found['article_id'];
      $REX['ADDON']['rexseo']['settings']['homelang'] = $found['clang'];

      // rewrite superglobals
      $_GET['ARTICLE_ID'] = $found['article_id'];
      if (!isset($_GET['CLANG'])) { $_GET['CLANG'] = $found['clang']; }
      $_REQUEST['ARTICLE_ID'] = $found['article_id'];
      if (!isset($_REQUEST['CLANG'])) { $_REQUEST['CLANG'] = $found['clang']; }
      
      // rewrite $REX;
      $REX['ARTICLE_ID'] = $found['article_id'];
      $REX['CUR_CLANG'] = $found['clang'];
    }
    
  }

  function getMetaField($articleID,$metafield="file",$defval="",$loop="")
  {
    $meta = OOArticle::getArticleById($articleID);
    $value = '';

    if (($meta->getValue($metafield))!="")
    {   $value=$meta->getValue($metafield);
    }
    else
    {  if ($loop=="LOOP") {
        $cat = OOCategory::getCategoryById($articleID);

        if ($cat->getParent())
        {  $cat = $cat->getParent();

          $value=self::getMetaField($cat->getValue('id'),$metafield,$defval,$loop);
        }
      }
    }

    if ($value == '')
    {
      $value = $defval;
    }


    return $value;
  }

}
?>