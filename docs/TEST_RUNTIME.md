# Test Runtime — X Bulk Dev Tools

## Prerequisites

- `IN_DEV` mode enabled
- At least one custom (non-protected) app installed

## Test Cases

### 1. Button Replacement

1. Navigate to ACP > System > Site Features > Applications
2. Verify sidebar shows "Bulk Dev Tools" button instead of "Build All"
3. Click the button — dialog should open

### 2. Bulk Build

1. Open Bulk Dev Tools dialog
2. Select "Build Application" action
3. Select 2-3 custom apps
4. Submit — MultipleRedirect should show progress
5. Verify redirect to applications page with success message

### 3. Bulk Compile JS

1. Open dialog, select "Compile JavaScript only"
2. Select apps, submit
3. Verify progress and completion

### 4. Rebuild & Download — ZIP

1. Select "Rebuild & Download" action
2. Ensure "All in one ZIP bundle" is selected
3. Select apps, submit
4. After processing, verify download results page appears
5. Click "Download All as ZIP" — should download a .zip containing .tar files

### 5. Rebuild & Download — Individual

1. Select "Rebuild & Download" action
2. Select "Individual .tar files"
3. Select apps, submit
4. After processing, verify download results page appears
5. Verify auto-download starts (hidden iframes)
6. Verify individual download links also work

### 6. Plugin Bulk Download — Individual

1. Navigate to ACP > System > Site Features > Plugins
2. Verify sidebar shows "Bulk Download Plugins" button under More Options
3. Click the button — dialog should open
4. Verify all plugins are listed and pre-checked
5. Ensure "Individual .xml files" is selected (default)
6. Submit — MultipleRedirect should show progress
7. Verify download results page appears with table of plugins
8. Verify auto-download starts (hidden iframes)
9. Verify individual download links also work

### 7. Plugin Bulk Download — ZIP

1. Open Bulk Download Plugins dialog
2. Select "All in one ZIP bundle"
3. Submit, wait for processing
4. Click "Download All as ZIP" — should download a .zip containing .xml files

### 8. Error Handling

1. If an app/plugin has broken state, verify other items still process
2. Verify error messages shown on completion

### 9. Sync Plugin Versions — In Sync

1. Navigate to ACP > System > Site Features > Plugins
2. Verify sidebar shows "Sync Plugin Versions" button (direct link, no dialog)
3. Click the button — full-page comparison table should appear
4. If all plugins match: green success message "All plugin versions are in sync"
5. Verify table shows Plugin, DB Version, Dev Version, Status columns
6. Verify in-sync plugins show green check icon

### 10. Sync Plugin Versions — Mismatch Detection

1. Manually change a plugin's `dev/versions.json` to add a higher version
2. Click "Sync Plugin Versions"
3. Verify warning message appears about mismatches
4. Verify mismatched plugins show red warning icon with "Out of Sync" status
5. Verify "Fix All Out-of-Sync" button appears

### 11. Sync Plugin Versions — Fix All

1. With a mismatch detected, click "Fix All Out-of-Sync"
2. Confirm the action
3. Verify redirect back to comparison page with success message showing count of fixed plugins
4. Verify all plugins now show "In Sync" status
5. Verify DB values match dev/versions.json (check via ACP plugin list or direct DB query)
