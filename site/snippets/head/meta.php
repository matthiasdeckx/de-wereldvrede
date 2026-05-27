<?php

	//Get all variables
	$pageTitle = $page->title();
	$siteTitle = $site->title();

	if (isset($page)) {

		if ($page->seo_title()->isNotEmpty()) {
			$seoTitle = $page->seo_title();
		} else {
			$seoTitle = $page->title();
		}

		if ($page->seo_description()->isNotEmpty()) {
			$seoDescription = $page->seo_description()->value();
		} elseif ($page->intendedTemplate()->name() === 'project' && $page->intro()->isNotEmpty()) {
			$seoDescription = $page->intro()->excerpt(300)->value();
		} elseif ($page->text()->isNotEmpty()) {
			$seoDescription = $page->text()->excerpt(160)->value();
		} else {
			$seoDescription = $site->seo_description()->value();
		}

		if ($page->og_title_toggle() == 'false' && $page->og_title()->isNotEmpty()) {
			$ogTitle = $page->og_title();
		} else {
			$ogTitle = $seoTitle;
		}

		if ($page->og_description_toggle() == 'false' && $page->og_description()->isNotEmpty()) {
			$ogDescription = $page->og_description()->value();
		} else {
			$ogDescription = $seoDescription;
		}

		if ($page->og_image()->isNotEmpty()) {
			$ogImage = $page->og_image()->toFile();
		} elseif ($page->cover()->isNotEmpty()) {
			$ogImage = $page->cover()->toFile();
		} elseif ($site->seo_image()->isNotEmpty()) {
			$ogImage = $site->seo_image()->toFile();
		} else {
			$ogImage = false;
		}

	} else {
		$seoTitle = $site->title();
		$seoDescription = $site->seo_description();
		$ogImage = $site->images()->find($site->seo_image()->toFile());
	}

?>

<title><?php if($page->isHomePage()): ?><?= $site->seo_prefix() ?> <?= $seoTitle ?><?php else: ?><?= $seoTitle ?> <?= $site->seo_suffix() ?><?php endif ?></title>

<meta name="description" content="<?= $seoDescription; ?>">

<meta property="og:title" content="<?php if($page->isHomePage()): ?><?= $site->seo_prefix() ?> <?= $ogTitle ?><?php else: ?><?= $ogTitle ?><?php endif ?>" />
<meta property="og:type" content="website" >
<meta property="og:site_name" content="<?= $siteTitle; ?>" />
<meta property="og:description" content="<?= $ogDescription; ?>" />
<meta property="og:url" content="<?= $page->url(); ?>" />
<?php if($ogImage): ?>
<meta property="og:image" content="<?= $ogImage->resize(1200)->url(); ?>" />
<meta property="og:image:width" content="<?= $ogImage->resize(1200)->width(); ?>" />
<meta property="og:image:height" content="<?= $ogImage->resize(1200)->height(); ?>" />
<?php endif ?>

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="<?= $siteTitle; ?>">
<meta name="twitter:title" content="<?= $ogTitle ?>">
<meta name="twitter:description" content="<?= $ogDescription; ?>">
<?php if($ogImage): ?>
<meta name="twitter:image" content="<?= $ogImage->resize(1200)->url(); ?>">
<?php endif ?>
