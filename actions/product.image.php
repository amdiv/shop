<?php

logData('INFO','Action wird gestartet',['fileName'=>__FILE__]);

if (count($routeParts) !== 5) {
    echo "Ungültige URL";
    logData('ERROR','wir erwarte 5 route parts',[
        'erhalteneRouteparts'=>count($routeParts) 
    ]);
    exit();
}
$slug = rawurldecode($routeParts[3]);
$fileName = $routeParts[4];
$sourceFilePath = STORAGE_DIR . '/productPictures/' . $slug . '/' . $fileName;


if (false === is_file($sourceFilePath)) {
    logData('WARNING','Bild wurde nicht gefunden, notfound.jpg wird angezeigt',['imagePath'=>$sourceFilePath]);
    http_response_code(404);
    $sourceFilePath = STORAGE_DIR . '/productPictures/notfound.jpg';
}
logData('INFO','Lade Bild',[
    'imagePath'=>$sourceFilePath
]);

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimetype = finfo_file($finfo, $sourceFilePath);

header('Content-Type:' . $mimetype);
readfile($sourceFilePath);