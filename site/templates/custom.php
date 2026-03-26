<?php
$showHeader = $page->show_header()->toBool();
$showFooter = $page->show_footer()->toBool();

$htmlFile = $page->html_file()->toFile();
$cssFile  = $page->css_file()->toFile();
$jsFile   = $page->js_file()->toFile();

if ($showHeader):
    snippet("header");
else: ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page->title() ?> | BSKA</title>
<?php endif; ?>

<?php if ($cssFile): ?>
    <link rel="stylesheet" href="<?= $cssFile->url() ?>?v=<?= $cssFile->modified() ?>">
<?php endif; ?>

<?php if (!$showHeader): ?>
</head>
<body>
<?php endif; ?>

<?php
if ($htmlFile):
    echo F::read($htmlFile->root());
elseif ($page->fallback_html()->isNotEmpty()):
    echo $page->fallback_html()->value();
endif;
?>

<?php if ($jsFile): ?>
    <script src="<?= $jsFile->url() ?>?v=<?= $jsFile->modified() ?>"></script>
<?php endif; ?>

<?php if ($showFooter):
    snippet("footer");
else: ?>
</body>
</html>
<?php endif; ?>
