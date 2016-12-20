<?php
//ini_set('display_errors', 1);

// création dossier fonts
if (!is_dir('./fonts')) {
  mkdir('./fonts');
}
define('FONT_PATTERN', '/\.ttf$/');
// POST
if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'POST') {
  // upload
  if (preg_match(FONT_PATTERN, $_FILES['add-font']['name'])) {
    $uploaddir = __DIR__.'/fonts/';
    $uploadfile = $uploaddir . basename($_FILES['add-font']['name']);
    move_uploaded_file($_FILES['add-font']['tmp_name'], $uploadfile);
    header('Location: index.php');
  }
  // lignes de texte
  $textsInline = filter_input(INPUT_POST, 'texts');
  // police de caractère
  $currentFont = filter_input(INPUT_POST, 'font');
  // font size
  $fontSize = filter_input(INPUT_POST, 'size');
}
if (!isset($textsInline) or $textsInline == '') {
  $textsInline = "Bienvenue\nà vous !";
}
if (!isset($currentFont) or $currentFont == '') {
  $currentFont = null;
}
if (!isset($fontSize) or !is_numeric($fontSize) or $fontSize < 1) {
  $fontSize = 80;
}
// chargement des polices
$fonts = [];
$fontDir = dir('./fonts');
while (false !== ($font = $fontDir->read())) {
  if (is_file("./fonts/$font") and preg_match(FONT_PATTERN, $font)) {
    $fonts[] = $font;
    if ($currentFont === null) {
      $currentFont = $font;
    }
  }
}
// fourni l'url de la requête
function imageQuery(array $texts, $font, $size, $zoom, $export = 0) {
  return './image.php?' .
    http_build_query([
      'font' => $font,
      'texts' => $texts,
      'zoom' => $zoom,
      'size' => $size,
      'export' => $export
    ]);
}
?>
<!doctype html>
<html>
  <head>
    <title>ForeText</title>
    <link href="static/css/style.css" type="text/css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" type="text/css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
  </head>
  <body>
    <div class="container">
      <h1>Foretext</h1>
      <div class="text-center">
        <form class="form-inline" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="add-font">Ajouter une police :</label>
            <a href="javascript:void(0);" class="btn btn-default" onclick="document.getElementById('add-font').click();">
              <span class="glyphicon glyphicon-plus"></span>
              Ajouter
            </a>
            <input type="file" class="hidden" accept=".ttf" name="add-font" id="add-font" />
          </div>
          <button type="submit" class="btn btn-danger">
            <span class="glyphicon glyphicon-send"></span>
            Envoyer
          </button>
        </form>
      </div>
      <div class="clearfix" style="height: 20px;"></div>
      <?php if (empty($fonts)) { die('</body></html>');} ?>
      <div class="row">
      <div class="col-md-6">
        <form method="POST" class="form">
          <div class="form-group">
            <label for="texts">Texte</label>
            <textarea name="texts" class="form-control" rows="5"><?php echo $textsInline; ?></textarea>
          </div>
          <div class="form-group">
            <label for="font">Police</label>
            <select name="font" class="form-control">
              <?php
              foreach ($fonts as $font) {
                echo '<option value="'.$font.'"'.($font == $currentFont ? ' selected' : '').'>'.$font.'</option>';
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label for="size">Police</label>
            <input type="number" class="form-control" name="size" value="<?php echo $fontSize; ?>" min="1" />
          </div>
          <button type="submit" class="btn btn-primary">
            <span class="glyphicon glyphicon-refresh"></span>
            Changer le texte
          </button>
          <a class="btn btn-success pull-right" href="<?php echo imageQuery(explode("\n", $textsInline), $currentFont, $fontSize, 2.8, 1); ?>">
            <span class="glyphicon glyphicon-cloud-download"></span>
            Télécharger l'image
          </a>
        </form>
      </div>
        <div class="col-md-6">
          <label>Aperçu</label>
          <div class="img-thumbnail transparent-bg">
            <img src="<?php echo imageQuery(explode("\n", $textsInline), $currentFont, $fontSize, 1) ?>" class="img-responsive"/>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
