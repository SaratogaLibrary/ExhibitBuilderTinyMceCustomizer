<?php
/**
 * Exhibit Builder TinyMCE Customizer
 *
 * Classic Omeka plugin: directory name must match class/file name
 * ExhibitBuilderTinyMceCustomizer + ExhibitBuilderTinyMceCustomizerPlugin.
 */

class ExhibitBuilderTinyMceCustomizerPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'config',
        'config_form',
        'admin_head',
    );

    protected $_options = array(
        'exhibit_tinymce_toolbar' => 'bold italic underline | bullist numlist | link unlink | table | code',
        'exhibit_tinymce_plugins' => 'lists,link,code,paste,media,autoresize,table',
        'exhibit_tinymce_paste_as_text' => 0,
        'exhibit_tinymce_extended_elements' => 'table[class|style],thead,tbody,tfoot,tr[class|style],td[colspan|rowspan|class|style],th[colspan|rowspan|class|style],colgroup,col[width|style]',
        'exhibit_tinymce_clean_office_tables' => 1,
        'exhibit_tinymce_allowed_css_styles' => 'width,text-align,vertical-align',
        'exhibit_tinymce_sync_html_purifier' => 1,
        'exhibit_tinymce_paste_preprocess_custom' => '',
        'exhibit_tinymce_advanced_config' => '',
    );

    public function hookInstall()
    {
        $this->_installOptions();
        if (get_option('exhibit_tinymce_sync_html_purifier')) {
            $this->_syncHtmlPurifierSettings();
        }
    }

    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        if (version_compare($oldVersion, '1.1.0', '<')) {
            if (get_option('exhibit_tinymce_advanced_config') === null) {
                set_option('exhibit_tinymce_advanced_config', '');
            }
        }
    }

    public function hookConfigForm()
    {
        $formValues = array(
            'toolbar' => get_option('exhibit_tinymce_toolbar'),
            'plugins' => get_option('exhibit_tinymce_plugins'),
            'pasteAsText' => (bool) get_option('exhibit_tinymce_paste_as_text'),
            'extendedElements' => get_option('exhibit_tinymce_extended_elements'),
            'cleanOfficeTables' => (bool) get_option('exhibit_tinymce_clean_office_tables'),
            'allowedStyles' => get_option('exhibit_tinymce_allowed_css_styles'),
            'syncPurifier' => (bool) get_option('exhibit_tinymce_sync_html_purifier'),
            'customPreprocess' => get_option('exhibit_tinymce_paste_preprocess_custom'),
            'advancedConfig' => get_option('exhibit_tinymce_advanced_config'),
        );
        include dirname(__FILE__) . '/config-form.php';
    }

    public function hookConfig($args)
    {
        $post = $args['post'];

        set_option('exhibit_tinymce_toolbar', trim($post['exhibit_tinymce_toolbar']));
        set_option('exhibit_tinymce_plugins', trim($post['exhibit_tinymce_plugins']));
        set_option('exhibit_tinymce_paste_as_text', !empty($post['exhibit_tinymce_paste_as_text']) ? 1 : 0);
        set_option('exhibit_tinymce_extended_elements', trim($post['exhibit_tinymce_extended_elements']));
        set_option('exhibit_tinymce_clean_office_tables', !empty($post['exhibit_tinymce_clean_office_tables']) ? 1 : 0);
        set_option('exhibit_tinymce_allowed_css_styles', trim($post['exhibit_tinymce_allowed_css_styles']));
        set_option('exhibit_tinymce_sync_html_purifier', !empty($post['exhibit_tinymce_sync_html_purifier']) ? 1 : 0);
        set_option(
            'exhibit_tinymce_paste_preprocess_custom',
            isset($post['exhibit_tinymce_paste_preprocess_custom'])
                ? $post['exhibit_tinymce_paste_preprocess_custom']
                : ''
        );
        set_option(
            'exhibit_tinymce_advanced_config',
            isset($post['exhibit_tinymce_advanced_config'])
                ? $post['exhibit_tinymce_advanced_config']
                : ''
        );

        if (!empty($post['exhibit_tinymce_sync_html_purifier'])) {
            $this->_syncHtmlPurifierSettings();
        }
    }

    /**
     * Parse advanced TinyMCE config textarea into key/value init params.
     *
     * One property per line, e.g.:
     * fontsize_formats: "12px 14px 16px 18px 24px 32px 48px"
     * branding: false
     * browser_spellcheck: true
     *
     * @param string $text
     * @return array
     */
    protected function _parseAdvancedConfig($text)
    {
        $params = array();
        $lines = preg_split('/\r\n|\r|\n/', (string) $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '//') === 0) {
                continue;
            }

            $line = rtrim($line, ", \t");
            $colonPos = strpos($line, ':');
            if ($colonPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $colonPos));
            $valueRaw = trim(substr($line, $colonPos + 1));
            if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }

            $params[$key] = $this->_decodeAdvancedConfigValue($valueRaw);
        }

        return $params;
    }

    /**
     * Decode a single TinyMCE config value from an advanced-config line.
     *
     * Prefers JSON (strings must use double quotes). Falls back to booleans,
     * numbers, or a plain string with surrounding quotes stripped.
     *
     * @param string $valueRaw
     * @return mixed
     */
    protected function _decodeAdvancedConfigValue($valueRaw)
    {
        $decoded = json_decode($valueRaw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        if (preg_match('/^(true|false|null)$/i', $valueRaw)) {
            return json_decode(strtolower($valueRaw));
        }

        if (is_numeric($valueRaw)) {
            return $valueRaw + 0;
        }

        return trim($valueRaw, " \t\"'");
    }

    /**
     * Merge table tags/attrs into Omeka's HTML Purifier allowlists.
     */
    protected function _syncHtmlPurifierSettings()
    {
        $tableElements = array(
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'colgroup', 'col',
        );
        $tableAttributes = array(
            'td.colspan', 'td.rowspan', 'th.colspan', 'th.rowspan', 'th.scope',
            'col.width', '*.style', '*.class',
        );

        $currentElements = get_option('html_purifier_allowed_html_elements');
        $elementsArray = $currentElements !== null && $currentElements !== ''
            ? array_map('trim', explode(',', $currentElements))
            : array();
        $mergedElements = array_unique(array_filter(array_merge($elementsArray, $tableElements)));
        set_option('html_purifier_allowed_html_elements', implode(',', $mergedElements));

        $currentAttributes = get_option('html_purifier_allowed_html_attributes');
        $attributesArray = $currentAttributes !== null && $currentAttributes !== ''
            ? array_map('trim', explode(',', $currentAttributes))
            : array();
        $mergedAttributes = array_unique(array_filter(array_merge($attributesArray, $tableAttributes)));
        set_option('html_purifier_allowed_html_attributes', implode(',', $mergedAttributes));
    }

    /**
     * Wrap Omeka.wysiwyg() on Exhibit Builder add/edit page and exhibit forms.
     *
     * Omeka.wysiwyg is a function (not a config object); settings must be passed
     * as the params argument to tinymce.init via that function.
     */
    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if (!$request) {
            return;
        }

        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $isExhibitBuilder = ($module === 'exhibit-builder' && $controller === 'exhibits');
        $isPageForm = in_array($action, array('add-page', 'edit-page'), true);
        $isExhibitForm = in_array($action, array('add', 'edit'), true);

        if (!$isExhibitBuilder || (!$isPageForm && !$isExhibitForm)) {
            return;
        }

        $toolbar = get_option('exhibit_tinymce_toolbar');
        $plugins = get_option('exhibit_tinymce_plugins');
        $pasteAsText = (bool) get_option('exhibit_tinymce_paste_as_text');
        $extendedElements = get_option('exhibit_tinymce_extended_elements');
        $cleanOffice = (bool) get_option('exhibit_tinymce_clean_office_tables');
        $allowedStyles = array_values(array_filter(array_map(
            'trim',
            explode(',', strtolower((string) get_option('exhibit_tinymce_allowed_css_styles')))
        )));
        $customPreprocess = (string) get_option('exhibit_tinymce_paste_preprocess_custom');
        $advancedParams = $this->_parseAdvancedConfig(get_option('exhibit_tinymce_advanced_config'));

        $config = array(
            'toolbar' => $toolbar,
            'plugins' => $plugins,
            'paste_as_text' => $pasteAsText,
            'extended_valid_elements' => $extendedElements,
            'clean_office_tables' => $cleanOffice,
            'allowed_css_styles' => $allowedStyles,
            'custom_preprocess' => $customPreprocess,
            'advanced_params' => $advancedParams,
        );

        $configJson = json_encode($config);
        queue_js_string($this->_getWysiwygOverrideScript($configJson));
    }

    /**
     * JS that wraps Omeka.wysiwyg before Exhibit Builder initializes editors.
     *
     * @param string $configJson JSON-encoded settings
     * @return string
     */
    protected function _getWysiwygOverrideScript($configJson)
    {
        return <<<JS
(function ($) {
    var exhibitTinyMceConfig = {$configJson};

    function cleanPastedContent(args) {
        if (!args || typeof args.content !== 'string') {
            return;
        }

        if (exhibitTinyMceConfig.clean_office_tables && args.content) {
            args.content = args.content.replace(/<!--\\[if[\\s\\S]*?endif\\]-->/gi, '');
            args.content = args.content.replace(/<\\/?(xml|meta|link|style)[^>]*>/gi, '');
            args.content = args.content.replace(/<(td|th)([^>]*)>\\s*<\\/\\1>/gi, '<\$1\$2>&nbsp;</\$1>');
        }

        var allowedStyles = exhibitTinyMceConfig.allowed_css_styles || [];
        if (allowedStyles.length > 0 && args.content) {
            var container = document.createElement('div');
            container.innerHTML = args.content;
            var styledElements = container.querySelectorAll('[style]');

            Array.prototype.forEach.call(styledElements, function (el) {
                var styleAttr = el.getAttribute('style') || '';
                var declarations = styleAttr.split(';');
                var retained = [];

                declarations.forEach(function (decl) {
                    var parts = decl.split(':');
                    if (parts.length < 2) {
                        return;
                    }
                    var prop = parts.shift().trim().toLowerCase();
                    var val = parts.join(':').trim();
                    if (prop && val && allowedStyles.indexOf(prop) !== -1 && prop.indexOf('mso-') !== 0) {
                        retained.push(prop + ': ' + val);
                    }
                });

                if (retained.length > 0) {
                    el.setAttribute('style', retained.join('; '));
                } else {
                    el.removeAttribute('style');
                }
            });
            args.content = container.innerHTML;
        }

        if (exhibitTinyMceConfig.custom_preprocess) {
            try {
                var customFunc = new Function('plugin', 'args', exhibitTinyMceConfig.custom_preprocess);
                customFunc(null, args);
            } catch (e) {
                if (window.console && console.error) {
                    console.error('Exhibit TinyMCE custom preprocess error:', e);
                }
            }
        }
    }

    function wrapWysiwyg() {
        if (typeof Omeka === 'undefined' || typeof Omeka.wysiwyg !== 'function') {
            return false;
        }
        if (Omeka.wysiwyg._exhibitTinyMceCustomized) {
            return true;
        }

        var original = Omeka.wysiwyg;
        Omeka.wysiwyg = function (params) {
            var customParams = {
                toolbar: exhibitTinyMceConfig.toolbar,
                plugins: exhibitTinyMceConfig.plugins,
                paste_as_text: !!exhibitTinyMceConfig.paste_as_text,
                paste_preprocess: function (plugin, args) {
                    cleanPastedContent(args);
                }
            };

            if (exhibitTinyMceConfig.extended_valid_elements) {
                customParams.extended_valid_elements = exhibitTinyMceConfig.extended_valid_elements;
            }

            // Built-in options, then advanced one-line params, then any caller overrides.
            return original.call(this, \$.extend(
                {},
                customParams,
                exhibitTinyMceConfig.advanced_params || {},
                params || {}
            ));
        };
        Omeka.wysiwyg._exhibitTinyMceCustomized = true;
        return true;
    }

    // Register early so this ready handler runs before page-form.php's ready.
    \$(document).ready(function () {
        if (!wrapWysiwyg()) {
            \$(window).on('load', wrapWysiwyg);
        }
    });
})(jQuery);
JS;
    }
}
