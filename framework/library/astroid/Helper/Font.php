<?php

/**
 * @package   Astroid Framework
 * @author    Astroid Framework Team https://astroidframe.work
 * @copyright Copyright (C) 2023 AstroidFrame.work.
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

namespace Astroid\Helper;

use Astroid\Framework;
use Astroid\Helper;

defined('_JEXEC') or die;

class Font
{
    public static $system_fonts = [
        "Arial, Helvetica, sans-serif" => 'Arial, Helvetica',
        "Arial Black, Gadget, sans-serif" => 'Arial Black, Gadget',
        "Bookman Old Style, serif" => 'Bookman Old Style',
        "Comic Sans MS, cursive" => 'Comic Sans MS',
        "Courier, monospace" => 'Courier',
        "Garamond, serif" => 'Garamond',
        "Georgia, serif" => 'Georgia',
        "Impact, Charcoal, sans-serif" => 'Impact, Charcoal',
        "Lucida Console, Monaco, monospace" => 'Lucida Console, Monaco',
        "Lucida Sans Unicode, sans-serif" => 'Lucida Sans Unicode',
        "MS Sans Serif, Geneva, sans-serif" => 'MS Sans Serif, Geneva',
        "MS Serif, New York, sans-serif" => 'MS Serif, New York',
        "Palatino Linotype, Book Antiqua, Palatino, serif" => 'Palatino Linotype, Book Antiqua, Palatino',
        "Tahoma, Geneva, sans-serif" => 'Tahoma, Geneva',
        "Times New Roman, Times, serif" => 'Times New Roman, Times',
        "Trebuchet MS, Helvetica, sans-serif" => 'Trebuchet MS, Helvetica',
        "Verdana, Geneva, sans-serif" => 'Verdana, Geneva'
    ];

    public static function googleFonts()
    {
        $app = \JFactory::getApplication();
        $fonts = Helper::getJSONData('webfonts');
        $options = [];

        if (!isset($fonts['items'])) {
            return $options;
        }

        foreach ($fonts['items'] as $font) {
            $variants = [];
            if (count($font['variants']) > 1) {
                foreach ($font['variants'] as $v) {
                    if ($v == 'regular') {
                        $variants[] = '400';
                    } else if ($v == 'italic') {
                        $variants[] = '400i';
                    } else {
                        $variants[] = str_replace('talic', '', $v);
                    }
                }
            }
            $value = str_replace(' ', '+', $font['family']);
            if (!empty($variants)) {
                $value .= ':' . implode(',', $variants);
            }
            $options[$font['category']][$value] = $font['family'];
        }
        return $options;
    }

    public static function getAllFonts()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $googleFonts = self::googleFonts();
        $rt_fonts   =   array([
            'value' => '__default',
            'text'  => \JText::_('TPL_ASTROID_OPTIONS_DEFAULT')
        ]);
        foreach (self::$system_fonts as $name => $system_font) {
            $rt_fonts[]     =   [
                'value' => $name,
                'text'  => $system_font
            ];
        }

        $uploadedFonts = self::getUploadedFonts(Framework::getTemplate()->template);

        if (!empty($uploadedFonts)) {
            foreach ($uploadedFonts as $uploaded_font) {
                $rt_fonts[]     =   [
                    'value' => $uploaded_font['id'],
                    'text'  => $uploaded_font['name']
                ];
            }
        }

        foreach ($googleFonts as $group => $fonts) {
            foreach ($fonts as $fontValue => $font) {
                $rt_fonts[]     =   [
                    'value' => $fontValue,
                    'text'  => $font
                ];
            }
        }
        return \json_encode($rt_fonts);
    }

    public static function getUploadedFonts($template)
    {
        Framework::getDebugger()->start('local-fonts');
        if (empty($template)) {
            return [];
        }

        require_once JPATH_LIBRARIES . '/' . 'astroid' . '/' . 'framework' . '/' . 'library' . '/' . 'FontLib' . '/' . 'Autoloader.php';
        $template_fonts_path        =   JPATH_SITE . "/templates/{$template}/fonts";
        $template_media_fonts_path  =   JPATH_SITE . "/media/templates/site/{$template}/fonts";
        $template_custom_fonts_path =   JPATH_SITE . "/images/{$template}/fonts";
        if (!file_exists($template_fonts_path) && !file_exists($template_media_fonts_path) && !file_exists($template_custom_fonts_path)) {
            return [];
        }
        $fonts = [];
        if (file_exists($template_media_fonts_path) || file_exists($template_fonts_path)) {
            if (file_exists($template_media_fonts_path)) $template_fonts_path = $template_media_fonts_path;
            $fonts  =   self::getLocalFonts($template_fonts_path);
        }

        if (file_exists($template_custom_fonts_path)) {
            $fonts  =   array_merge($fonts, self::getLocalFonts($template_custom_fonts_path));
        }
        Framework::getDebugger()->stop('local-fonts');
        return $fonts;
    }

    public static function getLocalFonts($template_fonts_path) {
        $fonts = [];
        $font_extensions = ['otf', 'ttf', 'woff'];
        foreach (scandir($template_fonts_path) as $font_path) {
            if (is_file($template_fonts_path . '/' . $font_path)) {
                $pathinfo = pathinfo($template_fonts_path . '/' . $font_path);
                if (in_array($pathinfo['extension'], $font_extensions)) {
                    $font = \FontLib\Font::load($template_fonts_path . '/' . $font_path);
                    $font->parse();
                    $fontname = $font->getFontFullName();
                    $fontid = 'library-font-' . Helper::slugify($fontname);
                    if (!isset($fonts[$fontid])) {
                        $fonts[$fontid] = [];
                        $fonts[$fontid]['id'] = $fontid;
                        $fonts[$fontid]['name'] = $fontname;
                        $fonts[$fontid]['files'] = [];
                    }
                    $fonts[$fontid]['files'][] = $font_path;
                }
            }
        }
        return $fonts;
    }

    public static function fontAwesomeIcons($html = false)
    {
        $icons = self::_getFAIcons();
        if ($html) {
            $array = [];
            $array[] = ['value' => '', 'name' => 'None'];
            foreach ($icons as $icon) {
                $array[] = ['value' => $icon['value'], 'name' => '<i class="' . $icon['value'] . '"></i> ' . $icon['name']];
            }
            $icons = $array;
        }
        return $icons;
    }

    public static function _getFAIcons()
    {

        $version = Helper\Constants::$fontawesome_version;
        if (file_exists(JPATH_ROOT . '/cache/astroid/fontawesome/free-' . $version . '-.json')) {
            return json_decode(file_get_contents(JPATH_ROOT . '/cache/astroid/fontawesome/free-' . $version . '-.json'), true);
        }

        $json = file_get_contents(ASTROID_MEDIA . '/vendor/fontawesome/metadata/icons.json');
        $json = \json_decode($json, true);

        $icons = [];
        foreach ($json as $icon => $info) {
            foreach ($info['styles'] as $style) {
                $icons[] = ['value' => 'fa' . substr($style, 0, 1) . ' fa-' . $icon, 'name' => $info['label'], 'type' => $style];
            }
        }
        Helper::putContents(JPATH_ROOT . '/cache/astroid/fontawesome/free-' . $version . '-.json', json_encode($icons));
        return $icons;
    }

    public static function getFontType($value)
    {
        $type = 'google';
        if (Helper::startsWith($value, 'library-font-')) {
            $type = 'local';
        }
        if (isset(self::$system_fonts[$value])) {
            $type = 'system';
        }
        return $type;
    }

    public static function getFontFamily($value)
    {
        $type = self::getFontType($value);
        switch ($type) {
            case 'google':
                $value = '"'.self::loadGoogleFont($value).'"';
                break;
            case 'local':
                $value = '"'.self::loadLocalFont($value).'"';
                break;
            case 'system':
                return $value;
                break;
        }
        return $value;
    }

    public static function loadGoogleFont($value)
    {
        $document = Framework::getDocument();

        $value = str_replace(',', ';', $value);
        $value = str_replace(':', ':ital,wght@', $value);

        $wght = substr($value, strpos($value, "@") + 1);

        $_value = explode(';', $wght);
        foreach ($_value as &$_v) {
            $_v = explode('i', $_v);
            if (count($_v) == 2) {
                $_v = '1,' . $_v[0];
            } else {
                $_v = '0,' . $_v[0];
            }
        }
        sort($_value);

        if (strpos($value, "@") > 0) {
            $value = str_replace($wght, implode(';', $_value), $value);
        }

        $document->addCustomTag('<link rel="preconnect" href="https://fonts.gstatic.com">');
        $document->addStyleSheet('https://fonts.googleapis.com/css2?family=' . $value . '&display=swap');

        @list($font, $variants) = explode(":", $value);

        if (preg_match('~[0-9]+~', $font)) {
            $font = "'{$font}'";
        }

        return str_replace('+', ' ', $font);
    }

    public static function loadLocalFont($value)
    {
        $template = Framework::getTemplate();
        $document = Framework::getDocument();
        $uploaded_fonts = $template->getFonts();
        $template_media_fonts_path  = JPATH_SITE . "/media/templates/site/{$template->template}/fonts";
        $template_custom_fonts_path = JPATH_SITE . "/images/{$template->template}/fonts";
        $font_custom_path           = \JURI::root() . "images/{$template->template}/fonts/";
        if (file_exists($template_media_fonts_path)) {
            $font_path      =       \JURI::root() . "media/templates/site/{$template->template}/fonts/";
        } else {
            $font_path      =       \JURI::root() . "templates/{$template->template}/fonts/";
        }
        if (isset($uploaded_fonts[$value])) {
            $files = $uploaded_fonts[$value]['files'];
            $value = $uploaded_fonts[$value]['name'];
            foreach ($files as $file) {
                if (file_exists($template_custom_fonts_path . '/' . $file)) {
                    $document->addStyleDeclaration('@font-face { font-family: "' . $value . '"; src: url("' . $font_custom_path . $file . '");}');
                } else {
                    $document->addStyleDeclaration('@font-face { font-family: "' . $value . '"; src: url("' . $font_path . $file . '");}');
                }
            }
        }
        return $value;
    }

    public static function loadFontAwesome()
    {
        $params = Helper::getPluginParams();
        $source = $params->get('astroid_load_fontawesome', "cdn");
        $document   =   Framework::getDocument();
        switch ($source) {
            case 'cdn':
                $document->addStyleSheet('media/system/css/joomla-fontawesome.min.css');
                $document->addStyleSheet("https://use.fontawesome.com/releases/v" . Helper\Constants::$fontawesome_version . "/css/all.css", ['data-version' => Helper\Constants::$fontawesome_version]);
                break;
            case 'local':
                $document->addStyleSheet('media/system/css/joomla-fontawesome.min.css');
                $document->addStyleSheet("vendor/fontawesome/css/all.min.css", ['data-version' => Helper\Constants::$fontawesome_version]);
                break;
            default:
                if (Framework::isAdmin()) {
                    Framework::getDocument()->addStyleSheet("vendor/fontawesome/css/all.min.css", ['data-version' => Helper\Constants::$fontawesome_version]);
                }
                break;
        }
    }
}
