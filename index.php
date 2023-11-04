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

$fileUrl = $_POST['file_url'];
$pdfPath = "{$_SERVER['DOCUMENT_ROOT']}/temp/temp.pdf";
$imagePath = "{$_SERVER['DOCUMENT_ROOT']}/temp/temp.png";


copy($fileUrl, $pdfPath);

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
    header('HTTP/1.1 200 OK');

    echo json_encode([
        'success' => $success,
        'content' => trim($content)
    ]);
}

exit;
