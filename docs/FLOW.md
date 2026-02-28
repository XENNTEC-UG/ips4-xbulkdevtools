# Flow — X Bulk Dev Tools

## Applications Flow

```
ACP > System > Site Features > Applications
  └── Sidebar: "Bulk Dev Tools" button (replaces "Build All")
      └── Dialog opens
          ├── Action: Compile JS / Build / Rebuild & Download
          ├── Download Mode (if Download): Individual / ZIP
          └── App Checkboxes (custom pre-checked)
              └── Submit
                  └── MultipleRedirect (full page)
                      ├── Per-app processing with progress bar
                      └── Completion:
                          ├── Build/CompileJS → redirect to apps page
                          └── Download → Download Results page
                              ├── "Download All as ZIP" button
                              └── Individual .tar links per app
```

## Plugins Flow

```
ACP > System > Site Features > Plugins
  └── Sidebar: "Bulk Download Plugins" button
      └── Dialog opens
          ├── Download Mode: Individual / ZIP
          └── Plugin Checkboxes (all pre-checked)
              └── Submit
                  └── MultipleRedirect (full page)
                      ├── Per-plugin XML export with progress bar
                      └── Completion → Plugin Download Results page
                          ├── "Download All as ZIP" button
                          └── Individual .xml links per plugin
```

## Applications Processing Sequence

1. Form submit → session stores `xbdt_apps`, `xbdt_download_mode`
2. Redirect to `xbdtProcess&action={action}` with CSRF
3. MultipleRedirect first call: reads session, builds `$data` array
4. Each step: load app → execute action → increment index → return progress
5. Final step: store errors + processed apps in session → return null
6. Finished callback: redirect based on action type

## Plugins Processing Sequence

1. Form submit → session stores `xbdt_plugin_ids`, `xbdt_plugin_download_mode`
2. Redirect to `xbdtPluginProcess` with CSRF
3. MultipleRedirect first call: reads session, builds `$data` array
4. Each step: load plugin → build XML → save to temp file → return progress
5. Final step: store errors + built info in session → return null
6. Finished callback: redirect to plugin download results page

## Error Handling

- Each app/plugin is wrapped in try/catch
- Errors collected in `$data['errors']` array
- On completion, errors shown as warning message (build/compilejs) or on results page (download)
- Processing continues even if one item fails
