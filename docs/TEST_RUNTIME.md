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

### 6. Error Handling

1. If an app has broken build state, verify other apps still process
2. Verify error messages shown on completion
