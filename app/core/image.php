<?php
// Process uploaded image and convert to JPEG, 800px width
function processImage(array $file)
{
    if ($file['error'] !== 0) return null;

    $tmpPath = $file['tmp_name'];
    $info = getimagesize($tmpPath);
    if (!$info) return null;

    $width = 800;
    $height = intval($info[1] * (800 / $info[0]));

    switch ($info['mime']) {
        case 'image/jpeg': $src = imagecreatefromjpeg($tmpPath); break;
        case 'image/png':  $src = imagecreatefrompng($tmpPath); break;
        case 'image/gif':  $src = imagecreatefromgif($tmpPath); break;
        case 'image/tiff': $src = imagecreatefromtiff($tmpPath); break;
        default: return null;
    }

    $dst = imagecreatetruecolor($width, $height);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

    $targetDir = __DIR__ . '/../uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $filename = $targetDir . uniqid('img_', true) . '.jpg';

    imagejpeg($dst, $filename, 90);

    imagedestroy($src);
    imagedestroy($dst);

    return $filename;
}
