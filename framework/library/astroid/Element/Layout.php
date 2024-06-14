<?php

/**
 * @package   Astroid Framework
 * @author    Astroid Framework Team https://astroidframe.work
 * @copyright Copyright (C) 2023 AstroidFrame.work.
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

namespace Astroid\Element;

use Astroid\Framework;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class Layout
{
    public static function render()
    {
        Framework::getDebugger()->log('Render Layout');
        $template = Framework::getTemplate();
        $layout = $template->getLayout();
        $devices = isset($layout['devices']) && $layout['devices'] ? $layout['devices'] : [
            [
                'code'=> 'lg',
                'icon'=> 'fa-solid fa-computer',
                'title'=> 'title'
            ]
        ];
        $content = '';
        foreach ($layout['sections'] as $section) {
            $section = new Section($section, $devices);
            $content .= $section->render();
        }
        Framework::getDebugger()->log('Render Layout');
        return $content;
    }

    public static function getSublayouts($template = '')
    {
        if (!$template) {
            $template   =   Framework::getTemplate()->template;
        }

        $layouts_path = JPATH_SITE . "/media/templates/site/{$template}/astroid/layouts/";
        if (!file_exists($layouts_path)) {
            return [];
        }
        $files = array_filter(glob($layouts_path . '*.json'), 'is_file');
        $layouts    =   [];
        foreach ($files as $file) {
            $json = file_get_contents($file);
            $data = \json_decode($json, true);
            $layout = ['title' => pathinfo($file)['filename'], 'desc' => '', 'thumbnail' => '', 'name' => pathinfo($file)['filename']];
            if (isset($data['title']) && !empty($data['title'])) {
                $layout['title'] = Text::_($data['title']);
            }
            if (isset($data['desc'])) {
                $layout['desc'] = Text::_($data['desc']);
            }
            if (isset($data['thumbnail']) && !empty($data['thumbnail'])) {
                $layout['thumbnail'] = Uri::root() . 'media/templates/site/' . $template . '/images/layouts/' . $data['thumbnail'];
            }
            $layouts[] = $layout;
        }
        return $layouts;
    }

    public static function getSubLayout($filename = '', $template = '')
    {
        if (!$filename) {
            return [];
        }
        if (!$template) {
            $template   =   Framework::getTemplate()->template;
        }

        $layout_path = JPATH_SITE . "/media/templates/site/{$template}/astroid/layouts/" . $filename . '.json';
        if (!file_exists($layout_path)) {
            return [];
        }
        $json = file_get_contents($layout_path);
        $data = \json_decode($json, true);

        return $data;
    }

    public static function deleteSublayouts($layouts = [], $template = '')
    {
        if (!count($layouts)) {
            return false;
        }
        if (!$template) {
            $template   =   Framework::getTemplate()->template;
        }

        $layouts_path   = JPATH_SITE . "/media/templates/site/{$template}/astroid/layouts/";
        $images_path    = JPATH_SITE . "/media/templates/site/{$template}/images/layouts/";
        foreach ($layouts as $layout) {
            if (file_exists($layouts_path . $layout . '.json')) {
                $json = file_get_contents($layouts_path . $layout . '.json');
                $data = \json_decode($json, true);
                unlink($layouts_path . $layout . '.json');
                if (file_exists($images_path . $data['thumbnail'])) {
                    unlink($images_path . $data['thumbnail']);
                }
            }
        }
        return true;
    }
}
