<?php

use Kirby\Cms\Html;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

/** @var \Kirby\Cms\Block $block */
$caption = $block->caption();

if (
	$block->location() === 'kirby' &&
	$videoFile = $block->video()->toFile()
) {
	$url = $videoFile->url();
	$attrs = array_filter([
		'autoplay'    => $block->autoplay()->toBool(),
		'controls'    => $block->controls()->toBool(),
		'loop'        => $block->loop()->toBool(),
		'muted'       => $block->muted()->toBool() || $block->autoplay()->toBool(),
		'playsinline' => $block->autoplay()->toBool(),
		'poster'      => $block->poster()->toFile()?->url(),
		'preload'     => $block->preload()->value(),
	]);
} else {
	$url = $block->url();
}

$embedW = null;
$embedH = null;

if (($block->location() ?? '') === 'web' && Str::length((string) $url) > 0) {
	try {
		$oembedUrl = null;
		if (Str::contains((string) $url, 'vimeo', true) === true) {
			$oembedUrl = 'https://vimeo.com/api/oembed.json?url=' . rawurlencode((string) $url);
		} elseif (Str::contains((string) $url, 'youtu', true) === true) {
			$oembedUrl = 'https://www.youtube.com/oembed?format=json&url=' . rawurlencode((string) $url);
		}

		if ($oembedUrl !== null) {
			$response = Remote::get($oembedUrl, ['timeout' => 5]);
			if ($response->code() === 200 && $response->content() !== null) {
				$data = json_decode($response->content(), true);
				if (
					is_array($data) === true &&
					isset($data['width'], $data['height']) === true
				) {
					$w = (int) $data['width'];
					$h = (int) $data['height'];
					if ($w > 0 && $h > 0) {
						$embedW = $w;
						$embedH = $h;
					}
				}
			}
		}
	} catch (Throwable) {
		// fall back to default ratio below
	}
}

$videoHtml = Html::video(
	(string) $url,
	$block->kirby()->option('kirbytext.video.options', []),
	$attrs ?? []
);

if (empty($videoHtml) === true) {
	return;
}

$ratio = ($embedW !== null && $embedH !== null) ? "{$embedW} / {$embedH}" : '16 / 9';
$isIframe = str_contains($videoHtml, '<iframe');

?>
<?php if ($isIframe === true): ?>
<figure>
	<div class="m-embed-responsive" style="--embed-aspect-ratio: <?= $ratio ?>;">
		<?= $videoHtml ?>
	</div>
	<?php if ($caption->isNotEmpty()): ?>
	<figcaption><?= $caption ?></figcaption>
	<?php endif ?>
</figure>
<?php else: ?>
<figure>
	<?= $videoHtml ?>
	<?php if ($caption->isNotEmpty()): ?>
	<figcaption><?= $caption ?></figcaption>
	<?php endif ?>
</figure>
<?php endif ?>
