<?php
/**
 * Plugin config form.
 *
 * @var array $formValues
 */
$view = get_view();
?>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_toolbar', __('TinyMCE Toolbar')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Toolbar buttons separated by spaces; use | for groups.'); ?></p>
        <?php echo $view->formText('exhibit_tinymce_toolbar', $formValues['toolbar']); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_plugins', __('TinyMCE Plugins')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Comma-separated TinyMCE plugin names. Include table if the toolbar uses the table button.'); ?></p>
        <?php echo $view->formText('exhibit_tinymce_plugins', $formValues['plugins']); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_paste_as_text', __('Paste as Plain Text')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Force clipboard pastes to strip HTML by default.'); ?></p>
        <?php echo $view->formCheckbox('exhibit_tinymce_paste_as_text', 1, array('checked' => $formValues['pasteAsText'])); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_extended_elements', __('Extended Valid Elements')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Comma-separated TinyMCE extended_valid_elements rules for tables and attributes.'); ?></p>
        <?php echo $view->formText('exhibit_tinymce_extended_elements', $formValues['extendedElements']); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_clean_office_tables', __('Clean Word/Excel Tables')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Strip Office conditional comments and empty cells when pasting.'); ?></p>
        <?php echo $view->formCheckbox('exhibit_tinymce_clean_office_tables', 1, array('checked' => $formValues['cleanOfficeTables'])); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_allowed_css_styles', __('Whitelisted Inline CSS Styles')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Comma-separated CSS property names to keep (e.g. width,text-align,vertical-align).'); ?></p>
        <?php echo $view->formText('exhibit_tinymce_allowed_css_styles', $formValues['allowedStyles']); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_sync_html_purifier', __('Sync with Omeka Security Settings')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Add table tags and attributes to Omeka HTML Purifier allowlists so saved blocks keep table markup.'); ?></p>
        <?php echo $view->formCheckbox('exhibit_tinymce_sync_html_purifier', 1, array('checked' => $formValues['syncPurifier'])); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_paste_preprocess_custom', __('Custom Preprocess JS Body')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Optional JavaScript body for paste_preprocess(plugin, args). Runs after built-in cleaners. Use with care.'); ?></p>
        <?php echo $view->formTextarea('exhibit_tinymce_paste_preprocess_custom', $formValues['customPreprocess'], array('rows' => 4, 'style' => 'font-family: monospace;')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('exhibit_tinymce_advanced_config', __('Advanced TinyMCE Init Options')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('One TinyMCE init property per line as key: value. Values should be valid JSON (use double quotes for strings). Blank lines and lines starting with # or // are ignored.'); ?>
            <br>
            <?php echo __('Examples:'); ?>
            <code>fontsize_formats: "12px 14px 16px 18px 24px 32px 48px"</code>,
            <code>branding: false</code>,
            <code>browser_spellcheck: true</code>
        </p>
        <?php echo $view->formTextarea(
            'exhibit_tinymce_advanced_config',
            $formValues['advancedConfig'],
            array(
                'rows' => 8,
                'style' => 'font-family: monospace;',
                'placeholder' => "fontsize_formats: \"12px 14px 16px 18px 24px 32px 48px\"\nbranding: false",
            )
        ); ?>
    </div>
</div>

<div class="field" style="background: #fdfdfd; border: 1px solid #e2e2e2; padding: 15px; margin-top: 20px;">
    <h3><?php echo __('Paste Preprocessor Test Sandbox'); ?></h3>
    <p class="explanation"><?php echo __('Paste markup from Excel or Word below to preview cleaning rules before saving settings.'); ?></p>

    <div style="display: flex; gap: 15px; margin-bottom: 12px;">
        <div style="flex: 1;">
            <label for="sandbox_input"><strong><?php echo __('Raw Pasted HTML'); ?></strong></label>
            <textarea id="sandbox_input" rows="8" style="width: 100%; font-family: monospace;" placeholder="<table><tr><td style='color:red; width:120px;'></td></tr></table>"></textarea>
        </div>
        <div style="flex: 1;">
            <label for="sandbox_output_html"><strong><?php echo __('Processed HTML Output'); ?></strong></label>
            <textarea id="sandbox_output_html" rows="8" style="width: 100%; font-family: monospace;" readonly></textarea>
        </div>
    </div>

    <button type="button" id="btn_run_sandbox" class="blue button" style="margin-bottom: 15px;"><?php echo __('Run Test Preprocessor'); ?></button>

    <div>
        <label><strong><?php echo __('Visual Render Preview'); ?></strong></label>
        <div id="sandbox_render_target" style="border: 1px dashed #bbb; padding: 12px; min-height: 70px; background: #fff; overflow-x: auto;">
            <em><?php echo __('Rendered output will display here.'); ?></em>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function ($) {
    $('#btn_run_sandbox').on('click', function () {
        var args = { content: $('#sandbox_input').val() };
        var cleanOffice = $('#exhibit_tinymce_clean_office_tables').is(':checked');
        var allowedStyles = ($('#exhibit_tinymce_allowed_css_styles').val() || '')
            .split(',')
            .map(function (s) { return s.trim().toLowerCase(); })
            .filter(Boolean);
        var customJs = $('#exhibit_tinymce_paste_preprocess_custom').val() || '';

        if (cleanOffice && args.content) {
            args.content = args.content.replace(/<!--\[if[\s\S]*?endif\]-->/gi, '');
            args.content = args.content.replace(/<\/?(xml|meta|link|style)[^>]*>/gi, '');
            args.content = args.content.replace(/<(td|th)([^>]*)>\s*<\/\1>/gi, '<$1$2>&nbsp;</$1>');
        }

        if (allowedStyles.length > 0 && args.content) {
            var container = document.createElement('div');
            container.innerHTML = args.content;
            Array.prototype.forEach.call(container.querySelectorAll('[style]'), function (el) {
                var styleAttr = el.getAttribute('style') || '';
                var retained = [];
                styleAttr.split(';').forEach(function (decl) {
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

        if (customJs.trim().length > 0) {
            try {
                var customFunc = new Function('plugin', 'args', customJs);
                customFunc(null, args);
            } catch (e) {
                alert('Error in Custom Preprocess JS: ' + e.message);
            }
        }

        $('#sandbox_output_html').val(args.content);
        $('#sandbox_render_target').html(args.content);
    });
});
</script>
