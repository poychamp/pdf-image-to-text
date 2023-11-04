<?php

use thiagoalessio\TesseractOCR\TesseractOCR;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json; charset=UTF-8");

set_time_limit(0);

$method = $_SERVER['REQUEST_METHOD'];

if ($method != 'POST') {
    exit;
}

require_once "./vendor/autoload.php";

function downloadFile($url, $path)
{
    $newfname = $path;
    $file = fopen ($url, 'rb');
    if ($file) {
        $newf = fopen ($newfname, 'wb');
        if ($newf) {
            while(!feof($file)) {
                fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
            }
        }
    }
    if ($file) {
        fclose($file);
    }
    if ($newf) {
        fclose($newf);
    }
}

$fileUrl = $_POST['file_url'];
$pdfPath = "{$_SERVER['DOCUMENT_ROOT']}/temp/temp.pdf";
$imagePath = "{$_SERVER['DOCUMENT_ROOT']}/temp/temp.png";


downloadFile($fileUrl, $pdfPath);

$pdf = new \Spatie\PdfToImage\Pdf($pdfPath);
$pdf->saveImage($imagePath);

$ocr = (new TesseractOCR($imagePath))->lang('deu', 'eng');

try {
    $content = $ocr->run();
    $success = true;
} catch (\Throwable $e) {
    $success = false;
    $content = $e->getMessage();
} finally {
    unlink($pdfPath);
    unlink($imagePath);

    header('HTTP/1.1 200 OK');

    echo json_encode([
        'success' => $success,
        'content' => trim($content)
    ]);
}

exit;