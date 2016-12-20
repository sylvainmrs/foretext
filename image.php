<?php
//ini_set('display_errors', 1);
/*
* CONFIG
*/
define('WIDTH_HD', 1900);
define('HEIGHT_HD', 1080);
define('ZOOM_MAX', 2.8);
define('ZOOM',
  min(
    ZOOM_MAX,
    filter_input(INPUT_GET, 'zoom', FILTER_VALIDATE_FLOAT, ['options' => [
      "default" => 1
    ]])
  )
);
define('FONT_SIZE', filter_input(INPUT_GET, 'size', FILTER_VALIDATE_FLOAT, ['options' => [
  "default" => 80
]]));
$fontGet = filter_input(INPUT_GET, 'font');
if (is_file('./fonts/' . $fontGet)) {
  define('FONT', './fonts/' . $fontGet);
} else {
  define('FONT', './fonts/Arial.ttf');
}
define('FOREGROUND_PADDING', 10);
define('TEXT_OFFSET', 30);
function zoom($size) {
  return $size * ZOOM;
}
function textForegrounded($image, $text, $textColor, $foregroundColor, $position = 0) {
  $fontSize = zoom(FONT_SIZE);
  $orientation = 0;
  $x = zoom(TEXT_OFFSET);
  $y = $position - zoom(TEXT_OFFSET);
  $textPosition = imagefttext($image, $fontSize, $orientation, $x, $y, $textColor, FONT, trim($text));
  imagefilledrectangle($image,
    $x - zoom(FOREGROUND_PADDING),
    $y + zoom(FOREGROUND_PADDING),
    $textPosition[4] + zoom(FOREGROUND_PADDING),
    $textPosition[5] - zoom(FOREGROUND_PADDING),
    $foregroundColor);
  imagefttext($image, $fontSize, $orientation, $x, $y, $textColor, FONT, trim($text));
  return $textPosition[5] + zoom(FOREGROUND_PADDING);
}

/*
* IMAGE
*/
header ('Content-Type: image/png');
//$texts = ["Bienvenue à vous,", "tres chers amis", "bien à vous"];
$texts = filter_input(INPUT_GET, 'texts', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$filename = substr(strtolower(preg_replace('/[^a-z\-]+/i', '', implode('-', $texts))), 0, 20).'-'.zoom(WIDTH_HD).'x'.zoom(HEIGHT_HD).'.png';
if (filter_input(INPUT_GET, 'export') == 1) {
header('Content-Disposition: attachment; filename="'.$filename.'"');
} else {
  header('Content-Disposition: inline; filename="'.$filename.'"');
}
$image = @imagecreatetruecolor(zoom(WIDTH_HD), zoom(HEIGHT_HD))
      or die('Impossible de créer un flux d\'image GD');
$whiteColor = imagecolorallocate($image, 255, 255, 255);
$marcelColor = imagecolorallocate($image, 39, 154, 200);
$blackColor = imagecolorallocate($image, 0, 0, 0);
imagecolortransparent($image, $blackColor);
$textOffset = zoom(HEIGHT_HD);
foreach (array_reverse($texts) as $text) {
  $textOffset = textForegrounded($image, $text, $whiteColor, $marcelColor, $textOffset);
}
imagepng($image);
imagedestroy($image);
