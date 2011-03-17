<?php
# Force template code to run first before layout wrapper
$content = (string) $content;

$menuLinks = array(
        'index' => array(
            'title' => 'Home',
            'img'   => 'home',
            'desc'  => 'Site Root. Latest News, Updates.',
        ),
        'rooms' => array(
            'title' => 'Manage Rooms',
            'img'   => 'manage',
            'desc'  => 'Add, edit and view rooms.',
        ),
        'days' => array(
            'title' => 'Manage Days',
            'img'   => 'manage',
            'desc'  => 'Add, edit and view days.',
        ),
        'types' => array(
            'title' => 'Manage Types',
            'img'   => 'manage',
            'desc'  => 'Add, edit and view types.',
        ),
        'events' => array(
            'title' => 'Manage Events',
            'img'   => 'manage',
            'desc'  => 'Add, edit and view events.',
        ),
        'import' => array(
            'title' => 'Import Data',
            'img'   => 'manage',
            'desc'  => 'Import Data.',
        ),
);

if (!isset($menuLinks[$currentPage]))
{
    foreach (array_keys($menuLinks) as $page)
    {
        if (substr($currentPage, 0, strlen($page)) == $page)
            $currentPage = $page;
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
    <title>The Online Programming Schedule (TOPS)<?php echo $title ? "::$title" : "" ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="designer" content="stt@sfu.ca" />
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/ui-darkness/jquery-ui.css" />
    
    <?php {
        echo HTML::style('static/css/a1.css', NULL, TRUE);
        foreach (Assets::getCSS() as $style)
            echo HTML::style("static/css/$style", NULL, TRUE);
        echo HTML::style('static/css/jquery_bar.css', NULL, TRUE);
        echo HTML::script('static/js/jquery_bar.js', NULL, TRUE); 
        foreach (Assets::getJS() as $js)
            echo HTML::script("static/js/$js", NULL, TRUE);
    } ?>
</head>

<body>
    <div id="container">
        <div id="user_login">
            <p><?php echo htmlentities($account->displayName); ?> (<?php echo htmlentities($account->email); ?>) (<?php echo html::anchor('auth/logout', 'Logout') ?>)</p>
        </div>

        <div id="header">
            <h1>The Online Programming Schedule (TOPS)</h1>
            <h2>&gt; <?php echo htmlentities($menuLinks[$currentPage]['title']) ?></h2>
        </div>

        <div id="menu">
            <ul>
            <?php foreach ($menuLinks as $url=>$link): ?>
                <?php if ($url == $currentPage): /* I hate this duplication, but littering it with ifs suck alot more */ ?>
                <li class="current">
                    <?php echo html::image('static/img/22_'.$link['img'].'.png', array('alt'=>$link['title']), TRUE); ?>
                    <?php echo htmlentities($link['title']) ?> 
                </li>
                <?php else: ?>
                <li>
                    <a href="<?php echo url::site("admin/$url"); ?>">
                        <?php echo html::image('static/img/16_'.$link['img'].'.png', array('alt'=>$link['title']), TRUE); ?>
                        <?php echo htmlentities($link['title']) ?>
                        <span><?php echo htmlentities($link['desc']) ?></span>
                    </a>
                </li>
                <?php endif ?>
            <?php endforeach ?>
            </ul>
        </div>
        
        <div id="content"><?php echo $content ?></div>
        
        <div id="footer">
            <a href="http://validator.w3.org/check?uri=referer">XHTML 1.1 Validated</a> |
            <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS 2.1 Validated</a> |
            Design by: stt@sfu.ca
        </div>
    </div>
    <div id="indicator" style="z-index: 1003; position: absolute; top: 15px; left: 15px; width: 32px; height: 32px; display: none" class="ui-dialog ui-widget-content">
        <?php echo html::image('static/img/ajax-loader.gif', array('alt'=>'indicator'), TRUE); ?>
    </div>
<script type="text/javascript">
<!--
	$.fn.bar.defaults.container = '#content';
    jQuery(document).ajaxStart(function() {
            var zindex = jQuery('ui-widget-overlay').css('z-index');
            jQuery('#indicator')
                .css('z-index', zindex+1)
                .show();
    });
    jQuery(document).ajaxError(function(e, xhr, settings, except) {
            jQuery('#indicator').hide();
            jQuery.fn.bar({ message: 'error trying to access: ' + settings.url + ' -- error: ' + except, background_color: '#F00'  });
    });
    jQuery(document).ajaxComplete(function() {
            jQuery('#indicator').hide();
    });
-->
</script>
<?php if (@$_GET['profile']): ?>
<div id="kohana-profiler"><?php echo View::factory('profiler/stats') ?></div>
<?php endif ?>
</body>
</html>
