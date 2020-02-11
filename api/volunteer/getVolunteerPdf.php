<?php
    require_once '../../lib/dompdf/autoload.inc.php';
    require_once '../../lib/dompdf/lib/html5lib/Parser.php';
    require_once '../../lib/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
    require_once '../../lib/dompdf/lib/php-svg-lib/src/autoload.php';
    require_once '../../lib/dompdf/src/Autoloader.php';

    Dompdf\Autoloader::register();

    use Dompdf\dompdf;

    $dompdf = new Dompdf();

    if($_SERVER['REQUEST_METHOD'] == 'GET') {
        $dompdf->set_option('defaultFont', 'Courier');

        $str = file_get_contents('./pdfHtmlTemplate.html');
        
        // Replacing the FirstName to $1
        $str = str_replace("[_FirstName]", "Vimal", $str);

        // Replacing the LastName to $2
        $str = str_replace('[_LastName]', 'Prasaath', $str);

        // echo $str.'<br/>';
        // Loading a string into HTML
        $dompdf->loadHtml($str);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
?>