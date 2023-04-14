<?php
//db verbindung
include 'DBVerbindung.php';

//FLYERUPLOAD
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

//Initialisieren der Variablen

$msgTEST='';
$adress ='0';
$plz ='0';
$seite ='0';
$art ='0';
$kategorie ='0';
$verein ='0';
$graffName ='0';
$polAussage ='0';
$offDarstellung ='0';
$farbe = '0';
$filename = '0';
$geoL = '0';
$geoA = '0';
$exif = '0';

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);

  // Metadaten submit
  $adress = htmlspecialchars($_POST['adress']);
  $plz = htmlspecialchars($_POST['plz']);
  $seite = htmlspecialchars($_POST['seite']);
  $art = htmlspecialchars(implode(', ', $_POST['art']));
  $kategorie = htmlspecialchars(implode(', ', $_POST['kategorie']));
  $verein = htmlspecialchars(implode(', ', $_POST['verein']));
  $graffName = htmlspecialchars(implode(', ', $_POST['graffName']));
  $polAussage = htmlspecialchars($_POST['polAussage']);
  $offDarstellung = htmlspecialchars($_POST['offDarstellung']);
  $farbe = htmlspecialchars(implode(', ', $_POST['farbe']));
  $filename = $target_file;
  
  //geodaten lesen
  $exif = exif_read_data($_FILES["fileToUpload"], 0, true);
  $exif2 = exif_read_data($_FILES["fileToUpload"]);
    if($exif && isset($exif['GPS'])){
        $GPSLatitudeRef = $exif['GPS']['GPSLatitudeRef'];
        $GPSLatitude    = $exif['GPS']['GPSLatitude'];
        $GPSLongitudeRef= $exif['GPS']['GPSLongitudeRef'];
        $GPSLongitude   = $exif['GPS']['GPSLongitude'];
        
        $lat_degrees = count($GPSLatitude) > 0 ? gps2Num($GPSLatitude[0]) : 0;
        $lat_minutes = count($GPSLatitude) > 1 ? gps2Num($GPSLatitude[1]) : 0;
        $lat_seconds = count($GPSLatitude) > 2 ? gps2Num($GPSLatitude[2]) : 0;
        
        $lon_degrees = count($GPSLongitude) > 0 ? gps2Num($GPSLongitude[0]) : 0;
        $lon_minutes = count($GPSLongitude) > 1 ? gps2Num($GPSLongitude[1]) : 0;
        $lon_seconds = count($GPSLongitude) > 2 ? gps2Num($GPSLongitude[2]) : 0;
        
        $lat_direction = ($GPSLatitudeRef == 'W' or $GPSLatitudeRef == 'S') ? -1 : 1;
        $lon_direction = ($GPSLongitudeRef == 'W' or $GPSLongitudeRef == 'S') ? -1 : 1;
        
        $latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60*60)));
        $longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60*60)));
    }else{
        $latitude = 'not found';
        $longitude = 'not found';		
    }

if($check !== false) {
    $msgCh = "File is an image - " . $check["mime"] . ".<br><br> ";
    $uploadOk = 1;
  } else {
    $msgCh = "File is not an image.<br><br>";
    $uploadOk = 0;
  }


// Check if file already exists
if (file_exists($target_file)) {
  $msgEx = "Sorry, file already exists.<br><br>";
  $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 100000000) {
  $msgSi = "Sorry, your file is too large.<br><br>";
  $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
  $msgFo = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br><br>";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  $msgOK = "Sorry, your file was not uploaded.<br><br>";
// if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

//Mysql insert
$sqlIn = 'INSERT INTO bilder (adress, plz, seite, art, kategorie, verein, graffName, polAussage, offDarstellung, filename, farbe) Values (:adress, :plz, :seite, :art, :kategorie, :verein, :graffName, :polAussage, :offDarstellung, :filename, :farbe)';
$stmtIn = $pdo->prepare($sqlIn);
$stmtIn->execute(['adress' => $adress, 'plz' => $plz, 'seite' => $seite, 'art' => $art, 'kategorie' => $kategorie, 'verein' => $verein, 'graffName' => $graffName, 'polAussage' => $polAussage, 'offDarstellung' => $offDarstellung, 'filename' => $filename, 'farbe' => $farbe]);
$msgTEST ='TEST: '.$exif['GPS'].' - '.$exif2['GPS']['GPSLatitude'].' - '.$longitude.' - '.$latitude;
    $msgOK = 'The file '. htmlspecialchars( basename( $_FILES['fileToUpload']['name'])). ' has been uploaded.';
  } else {
    $msgOK = "Sorry, there was an error uploading your file.<br><br>";
  }
}}
//Bild anhand id löschen aus DB
$delmsg = '';
if(isset($_GET['_delID'])){
$_ID = htmlspecialchars($_GET['_delID']);
if (!empty($_ID)){

$sql = 'SELECT * FROM bilder WHERE _ID = :_ID';
$stmt= $pdo->prepare($sql);
$stmt->execute(['_ID' => $_ID]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
$test = $row['filename'];
if($file = fopen($row['filename'],"w")){
fclose($file);
unlink($row['filename']);
}
}

$sqlDel = 'DELETE FROM bilder WHERE _ID = :_ID';
$stmtDel= $pdo->prepare($sqlDel);
$stmtDel->execute(['_ID' => $_ID]);
$delmsg = '<br>"'.$test.'" wurde erfolgreich gelöscht <br><br><a href="https://dhgraff-leipzig.ibrave.host/uploader/uploader.php">Jetzt Seite neu laden!</a>';
} else {
$delmsg ='<br>Fehler beim löschen.';
}
}

?>

<!DOCTYPE html>
<html>
<style>
table, th, td {
  border:1px solid black;
  border-collapse: collapse;
}
th, td {
  padding: 15px;
}
</style>
<body>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
  <br>Bild hochladen:<br><br>
  <input type="file" name="fileToUpload" id="fileToUpload"><br><br>

 Adresse (Str. & HNr.):  <input maxlength="50" type="text" name="adress" id="adress"> [Max. 50 Zeichen]<br><br>

 PLZ:  <input maxlength="5" type="text" size="5" name="plz" id="plz"> [Only 5 Digits]<br><br>

 Geo LOngit:  <input maxlength="50" type="text" name="geoL" id="geoL" disabled="true"> [Auto]<br><br>

 Geo LAddit:  <input maxlength="50" type="text" name="geoA" id="geoA" disabled="true"> [Auto]<br><br>

 Aufnahmedatum:  <input type="date" name="date" id="date" disabled="true"> [Auto]<br><br>

 Seite:  <br><input type="radio" name="seite" id="seiteV" checked value="Vorne">Vorne<br>
   <input type="radio" name="seite" id="seiteH" value="Hinten">Hinten<br><br>

 Art: <br> <input type="checkbox" name="art[]" id="vollbemalt" value="Voll bemalt"> Voll bemalt<br>
<input type="checkbox" name="art[]" id="taggs" value="Taggs"> Taggs<br>
<input type="checkbox" name="art[]" id="sticker" value="Sticker"> Sticker<br>
<input type="checkbox" name="art[]" id="plakate" value="Plakate"> Plakate<br>
<input type="checkbox" name="art[]" id="leer" value="Leer"> Leer<br><br>

 Kategorie & Inhalt:
<table>
<tr>
<th>Kategorie</th>
<th>Inhalte</th>
</tr><tr>
<td>
<input type="checkbox" name="kategorie[]" id="katFuss" value="Fussball"> Fussball
</td>
<td>
<input type="checkbox" name="verein[]" id="vereinBSG" value="BSG"> BSG CHEMIE<br>
<input type="checkbox" name="verein[]" id="vereinLOK" value="LOK"> LOK Leipzig<br>
<input type="checkbox" name="verein[]" id="vereinRSL" value="RSL"> Roter Stern (RSL)<br>
<input type="checkbox" name="verein[]" id="vereinRBL" value="RBL"> RedBull Leipzig<br>
<input type="checkbox" name="verein[]" id="vereinAndere" value="Andere"> Andere<br><br>
<input type="checkbox" name="verein[]" id="vereinkonflikt" value="konflikt"> Konflikt/tlw. uebermalt/...
</td></tr><tr>
<td>
<input type="checkbox" name="kategorie[]" id="katGraff" value="Graffitis/Taggs"> Crew/Artist Namen (Taggs oder Graffiti)
</td><td>
<input type="text" maxLenght="150" size="50" name="graffName[]" id="graffName"> <br>
<input type="checkbox" name="graffName[]" id="grafflesbar" value="Unleserlich"> Unleserlich<br>(Bei mehreren: getrennt durch KOMMA)

</td>
</tr>

<tr>
<td>
<input type="checkbox" name="kategorie[]" id="katPol" value="Politische Inhalte"> Politisches
</td>
<td>
<input type="text" maxLenght="500" size="100" name="polAussage" id="polAussage"><br>(Bei mehreren mit KOMMA trennen)
</td>
</tr>
<tr>
<td>
<input type="checkbox" name="kategorie[]" id="katOffiziell" value="Offiziele Bemalung"> Offiziel bemalt
</td>
<td>
<input type="text" maxLenght="100" size="100" name="offDarstellung" id="offDarstellung"><br>(Bsp.: Natur, Stadt, Wald, Tier, Menschen, Wasser, Feuer, ...)<br>(Bei mehreren mit KOMMA trennen)
</td>
</tr>

 <tr>
<td>
Farbe(n)<br>(Nur, wenn großflächig Farben verwendet wurden)
</td><td>
<input type="checkbox" name="farbe[]" id="frot" value="Rot"> Rot<br>
<input type="checkbox" name="farbe[]" id="fschwarz" value="Schwarz"> Schwarz<br>
<input type="checkbox" name="farbe[]" id="fchrome" value="Chrome"> Chrome<br>
<input type="checkbox" name="farbe[]" id="fgruen" value="Gruen"> Gruen<br>
<input type="checkbox" name="farbe[]" id="fgelb" value="Gelb"> Gelb<br>
<input type="checkbox" name="farbe[]" id="fblau" value="Blau"> Blau<br>
<input type="checkbox" name="farbe[]" id="flila" value="Lila"> Lila<br>
<input type="checkbox" name="farbe[]" id="ftuerkies" value="Tuerkies"> Tuerkies<br>
<input type="checkbox" name="farbe[]" id="fandere" value="Andere"> Andere
</td>
</tr>
  
</table>
<br>

<input type="submit" value="Upload Image" name="submit"><br><br>
</form>
<?php
echo ''. $msgCh.$msgEx.$msgFO.$msgOK.$msgTEST;
echo ''.$delmsg;
?>

<br><br>
Letzter Eintrag in DB:<br>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" >
<?php
//Liste anzeigen
//counter
$sqlSh = 'select * from bilder';
$stmtSh = $pdo->prepare($sqlSh);
$stmtSh->execute([]);
$postCount = $stmtSh->rowCount();

//ab hier nur letzter eintrag anzeigen
$sqlSh = 'select * from bilder ORDER BY _ID DESC LIMIT 1';
$stmtSh = $pdo->prepare($sqlSh);
$stmtSh->execute([]);
//bis hier nur letzter eintrag anzeigen

while($row = $stmtSh->fetch(PDO::FETCH_ASSOC)){
$file = $row['filename'];
$filesize = filesize($file); // bytes
$filesize = round($filesize / 1024, 2); // kilobytes with two digits

echo '<br>ID: '.$row['_ID'].' Timestamp: '.$row['date'];
echo '<br>Name: '.$row['filename'].' ['.$filesize.' KB]';
echo '<br>GeoL: '.$geoL.'; GeoA: '.$geoA;
echo '<br><a href="https://dhgraff-leipzig.ibrave.host/uploader/uploader.php?_delID='.$row['_ID'].'"></a>Löschen (derzeit deaktiviert)<br>';
echo '<br>Gesamt: '.$postCount.' Bilder in DB';
}
?>
</form>
<p>
<br><br>Infos und Fragen:<br>
Bitte Read-Me im GitHub Lesen! Dort ist vieles für ein einheitliches Eintragen angegeben.
<br></p>
<?php
//$exif = exif_read_data('uploads/20230211_041505.jpg', 0, true);
//echo "test2.jpg:<br />\n";
//foreach ($exif as $key => $section) {
//    foreach ($section as $name => $val) {
//        echo "$key.$name: $val<br />\n";
//    }
//}
// get geo-data from image FUNKTIONIERT!!!
function get_image_location($file) {
  if (is_file($file)) {
      $info = exif_read_data($file);
      if ($info !== false) {
          $direction = array('N', 'S', 'E', 'W');
          if (isset($info['GPSLatitude'], $info['GPSLongitude'], $info['GPSLatitudeRef'], $info['GPSLongitudeRef'], $info['FileName']) &&
              in_array($info['GPSLatitudeRef'], $direction) && in_array($info['GPSLongitudeRef'], $direction)) {

              $lat_degrees_a = explode('/',$info['GPSLatitude'][0]);
              $lat_minutes_a = explode('/',$info['GPSLatitude'][1]);
              $lat_seconds_a = explode('/',$info['GPSLatitude'][2]);
              $lng_degrees_a = explode('/',$info['GPSLongitude'][0]);
              $lng_minutes_a = explode('/',$info['GPSLongitude'][1]);
              $lng_seconds_a = explode('/',$info['GPSLongitude'][2]);

              $lat_degrees = $lat_degrees_a[0] / $lat_degrees_a[1];
              $lat_minutes = $lat_minutes_a[0] / $lat_minutes_a[1];
              $lat_seconds = $lat_seconds_a[0] / $lat_seconds_a[1];
              $lng_degrees = $lng_degrees_a[0] / $lng_degrees_a[1];
              $lng_minutes = $lng_minutes_a[0] / $lng_minutes_a[1];
              $lng_seconds = $lng_seconds_a[0] / $lng_seconds_a[1];

              $lat = (float) $lat_degrees + ((($lat_minutes * 60) + ($lat_seconds)) / 3600);
              $lng = (float) $lng_degrees + ((($lng_minutes * 60) + ($lng_seconds)) / 3600);
              $lat = number_format($lat, 7);
              $lng = number_format($lng, 7);

              //If the latitude is South, make it negative. 
              //If the longitude is west, make it negative
              $lat = $info['GPSLatitudeRef'] == 'S' ? $lat * -1 : $lat;
              $lng = $info['GPSLongitudeRef'] == 'W' ? $lng * -1 : $lng;

              return array(
                'filename' => $info['FileName'],
                  'lat' => $lat,
                  'lng' => $lng
                  
              );
          }


      }
  }

  return false;
}
$testgeo[] = get_image_location('uploads/20230211_041505.jpg');
echo '<pre>'; print_r($testgeo); echo '</pre>';

// alle Files aus Upload folder
$fileSystemIterator = new FilesystemIterator('uploads');

$entries = array();
foreach ($fileSystemIterator as $fileInfo){
    $entries[] = $fileInfo->getFilename();
    
}

echo '<pre>'; print_r($entries); echo '</pre>';
foreach ($entries as $entry)  {

   $geoData[] = get_image_location('uploads/' . $entry);
}

$combinedArray = array_combine($entries, $geoData);
echo '<pre>'; print_r($combinedArray); echo '</pre>';
// Save array $geodata to JSON
$jsonData = json_encode($geoData);
file_put_contents('geodata.json', $jsonData); // saves the JSON data to a file named "geodata.json" in the same directory as the PHP script.
// Save array $geodata to JSON
 // saves the JSON data to a file named "geodata.json" in the same directory as the PHP script.

// The above code saves the JSON data to a file named "geodata.json" in the same directory as the PHP script.
// To download the geodata.json file to the computer, you can simply navigate to the directory where the file is located and download it manually. Alternatively, you can use PHP to force a download of the file by adding the following code:

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="geodata.json"');
readfile('geodata.json');

// This code sets the appropriate headers to indicate that the file being downloaded is a JSON file, and specifies the filename as "geodata.json". The readfile() function then reads the contents of the file and outputs it to the browser, triggering a download prompt.

?>
</body>
</html>
