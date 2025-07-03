<?php

global $dompdfOptions;
$dompdfOptions = new Dompdf\Options();

/**
 * The location of a temporary directory.
 *
 * The directory specified must be writeable by the webserver process.
 * The temporary directory is required to download remote images and when
 * using the PFDLib back end.
 */
$dompdfOptions->set('temp_dir', sys_get_temp_dir());

/**
 * Whether to make font subsetting or not.
 */
$dompdfOptions->set('enable_font_subsetting', false);

/**
 * The PDF rendering backend to use
 *
 * Valid settings are 'PDFLib', 'CPDF' (the bundled R&OS PDF class), 'GD' and
 * 'auto'.  'auto' will look for PDFLib and use it if found, or if not it will
 * fall back on CPDF.  'GD' renders PDFs to graphic files.  {@link
 * Canvas_Factory} ultimately determines which rendering class to instantiate
 * based on this setting.
 *
 * Both PDFLib & CPDF rendering backends provide sufficient rendering
 * capabilities for dompdf, however additional features (e.g. object,
 * image and font support, etc.) differ between backends.  Please see
 * {@link PDFLib_Adapter} for more information on the PDFLib backend
 * and {@link CPDF_Adapter} and lib/class.pdf.php for more information
 * on CPDF.  Also see the documentation for each backend at the links
 * below.
 *
 * The GD rendering backend is a little different than PDFLib and
 * CPDF.  Several features of CPDF and PDFLib are not supported or do
 * not make any sense when creating image files.  For example,
 * multiple pages are not supported, nor are PDF 'objects'.  Have a
 * look at {@link GD_Adapter} for more information.  GD support is new
 * and experimental, so use it at your own risk.
 *
 * @link http://www.pdflib.com
 * @link http://www.ros.co.nz/pdf
 * @link http://www.php.net/image
 */
$dompdfOptions->set('pdf_backend', 'CPDF');

/**
 * PDFlib license key
 *
 * If you are using a licensed, commercial version of PDFlib, specify
 * your license key here.  If you are using PDFlib-Lite or are evaluating
 * the commercial version of PDFlib, comment out this setting.
 *
 * @link http://www.pdflib.com
 *
 * If pdflib present in web server and auto or selected explicitely above,
 * a real license code must exist!
 */
//def("DOMPDF_PDFLIB_LICENSE", "your license key here");

/**
 * html target media view which should be rendered into pdf.
 * List of types and parsing rules for future extensions:
 * http://www.w3.org/TR/REC-html40/types.html
 *   screen, tty, tv, projection, handheld, print, braille, aural, all
 * Note: aural is deprecated in CSS 2.1 because it is replaced by speech in CSS 3.
 * Note, even though the generated pdf file is intended for print output,
 * the desired content might be different (e.g. screen or projection view of html file).
 * Therefore allow specification of content here.
 */
$dompdfOptions->set('default_media_type', 'screen');

/**
 * The default paper size.
 *
 * North America standard is "letter"; other countries generally "a4"
 *
 * @see CPDF_Adapter::PAPER_SIZES for valid sizes
 */
$dompdfOptions->set('default_paper_size', 'letter');

/**
 * The default font family
 *
 * Used if no suitable fonts can be found. This must exist in the font folder.
 * @var string
 */
$dompdfOptions->set('default_font', 'serif');

/**
 * Image DPI setting
 *
 * This setting determines the default DPI setting for images and fonts.  The
 * DPI may be overridden for inline images by explictly setting the
 * image's width & height style attributes (i.e. if the image's native
 * width is 600 pixels and you specify the image's width as 72 points,
 * the image will have a DPI of 600 in the rendered PDF.  The DPI of
 * background images can not be overridden and is controlled entirely
 * via this parameter.
 *
 * For the purposes of DOMPDF, pixels per inch (PPI) = dots per inch (DPI).
 * If a size in html is given as px (or without unit as image size),
 * this tells the corresponding size in pt.
 * This adjusts the relative sizes to be similar to the rendering of the
 * html page in a reference browser.
 *
 * In pdf, always 1 pt = 1/72 inch
 *
 * Rendering resolution of various browsers in px per inch:
 * Windows Firefox and Internet Explorer:
 *   SystemControl->Display properties->FontResolution: Default:96, largefonts:120, custom:?
 * Linux Firefox:
 *   about:config *resolution: Default:96
 *   (xorg screen dimension in mm and Desktop font dpi settings are ignored)
 *
 * Take care about extra font/image zoom factor of browser.
 *
 * In images, <img> size in pixel attribute, img css style, are overriding
 * the real image dimension in px for rendering.
 *
 * @var int
 */
if(!defined('DPI')){
    define('DPI', 300);
}
$dompdfOptions->set('dpi', DPI);

/**
 * Enable inline PHP
 *
 * If this setting is set to true then DOMPDF will automatically evaluate
 * inline PHP contained within <script type="text/php"> ... </script> tags.
 *
 * Enabling this for documents you do not trust (e.g. arbitrary remote html
 * pages) is a security risk.  Set this option to false if you wish to process
 * untrusted documents.
 *
 * @var bool
 */
$dompdfOptions->set('enable_php', true);

/**
 * Enable inline Javascript
 *
 * If this setting is set to true then DOMPDF will automatically insert
 * JavaScript code contained within <script type="text/javascript"> ... </script> tags.
 *
 * @var bool
 */
$dompdfOptions->set('enable_javascript', false);

/**
 * Enable remote file access
 *
 * If this setting is set to true, DOMPDF will access remote sites for
 * images and CSS files as required.
 * This is required for part of test case www/test/image_variants.html through www/examples.php
 *
 * Attention!
 * This can be a security risk, in particular in combination with DOMPDF_ENABLE_PHP and
 * allowing remote access to dompdf.php or on allowing remote html code to be passed to
 * $dompdf = new DOMPDF(); $dompdf->load_html(...);
 * This allows anonymous users to download legally doubtful internet content which on
 * tracing back appears to being downloaded by your server, or allows malicious php code
 * in remote html pages to be executed by your server with your account privileges.
 *
 * @var bool
 */
$dompdfOptions->set('enable_remote', true);

/**
 * A ratio applied to the fonts height to be more like browsers' line height
 */
$dompdfOptions->set('font_height_ratio', 1.2);

/**
 * Use the more-than-experimental HTML5 Lib parser
 */
$dompdfOptions->set('enable_html5_parser', false);

// ### End of user-configurable options ###

/**
 * Ensure that PHP is working with text internally using UTF8 character encoding.
 */
mb_internal_encoding('UTF-8');
