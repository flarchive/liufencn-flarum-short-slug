import app from 'flarum/admin/app';

app.initializers.add('liufencn-short-slug', (app) => {
    // Flarum 2.0 moved the admin registry: `app.extensionData` still exists as a
    // fallback, but new code should use `app.registry`. The extension id is
    // derived from the composer package name (liufencn/flarum-short-slug ->
    // liufencn-short-slug), not from the folder the extension lives in.
    const registry = app.extensionData || app.registry;

    registry
        .for('liufencn-short-slug')
        .registerSetting({
            setting: 'liufencn-short-slug.enabled',
            label: app.translator.trans('liufencn-short-slug.admin.settings.enabled_label'),
            help: app.translator.trans('liufencn-short-slug.admin.settings.enabled_help'),
            type: 'switch',
        })
        .registerSetting({
            setting: 'liufencn-short-slug.allow_custom',
            label: app.translator.trans('liufencn-short-slug.admin.settings.allow_custom_label'),
            help: app.translator.trans('liufencn-short-slug.admin.settings.allow_custom_help'),
            type: 'switch',
        });
});
