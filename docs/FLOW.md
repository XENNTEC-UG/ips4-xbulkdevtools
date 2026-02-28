# Flow — X Bulk Dev Tools

## UI Flow

```
ACP > System > Site Features > Applications
  └── Sidebar: "Bulk Dev Tools" button (replaces "Build All")
      └── Dialog opens
          ├── Action: Build / Compile JS / Rebuild & Download
          ├── Download Mode (if Download): ZIP / Individual
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

## Processing Sequence

1. Form submit → session stores `xbdt_apps`, `xbdt_download_mode`
2. Redirect to `xbdtProcess&action={action}` with CSRF
3. MultipleRedirect first call: reads session, builds `$data` array
4. Each step: load app → execute action → increment index → return progress
5. Final step: store errors + processed apps in session → return null
6. Finished callback: redirect based on action type

## Error Handling

- Each app is wrapped in try/catch
- Errors collected in `$data['errors']` array
- On completion, errors shown as warning message (build/compilejs) or on results page (download)
- Processing continues even if one app fails
