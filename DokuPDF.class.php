<?php
/**
 * Wrapper around the headless Chromium engine
 *
 * @author Frieder Schrempf <dev@fris.de>
 */
global $conf;
if(!defined('_MPDF_TEMP_PATH')) define('_MPDF_TEMP_PATH', $conf['tmpdir'] . '/dwpdf/' . rand(1, 1000) . '/');

require_once __DIR__ . '/vendor/autoload.php';
use HeadlessChromium\BrowserFactory;

/**
 * Class DokuPDF
 * Some DokuWiki specific extentions
 */
class DokuPDF {
    protected $pagewidth;
    protected $pageheight;

    /**
     * DokuPDF constructor.
     *
     * @param string $pagesize
     * @param string $orientation
     * @param int $fontsize
     */
    function __construct($pw = 21.0, $ph = 29.7) {
        $this->pagewidth = $pw / 2.54;
        $this->pageheight = $ph / 2.54;

        io_mkdir_p(_MPDF_TEMP_PATH);
    }

    /**
     * Cleanup temp dir
     */
    function __destruct() {
        io_rmdir(_MPDF_TEMP_PATH, true);
    }

    /**
     * Request a PDF file to be rendered from HTML input
     */
    public function requestPDF($executable, $html, $header, $footer, $cachefile) {
        $browserFactory = new BrowserFactory($executable);
        $browser = $browserFactory->createBrowser(['sendSyncDefaultTimeout' => 10000]);
        $page = $browser->createPage();
        $options = [
            'displayHeaderFooter' => true,
            'headerTemplate' => $header,
            'footerTemplate' => $footer,
            'paperWidth' => $this->pagewidth,
            'paperHeight' => $this->pageheight,
            'marginTop' => 0.6,
            'marginBottom' => 0.6,
        ];
        file_put_contents(_MPDF_TEMP_PATH . 'tmp.html', $html);
        $page->navigate('file://' . _MPDF_TEMP_PATH . 'tmp.html')->waitForNavigation();
        $page->pdf($options)->saveToFile($cachefile);
    }
}
