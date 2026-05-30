# GeoTrack Pro: InfinityFree Migration Guide

You have successfully migrated the backend to PHP. To complete the connection between your Android APK and the new InfinityFree server, follow these steps:

## 1. Database Setup
1. Log in to your InfinityFree **Control Panel**.
2. Create a new **MySQL Database**.
3. Open **phpMyAdmin** for that database.
4. Import the `database.sql` file provided in the `php/` folder.

## 2. PHP Configuration
1. Open `php/db.php`.
2. Update the `$db_host`, `$db_name`, `$db_user`, and `$db_pass` with the credentials from your InfinityFree Control Panel.
3. Upload all files from the `php/` folder to your InfinityFree `htdocs/` directory.

## 3. Connecting the APK (Direct Link)
To make the APK connect directly to your InfinityFree site without using GitHub or Tunnels, you have two options:

### Option A: Update GitHub (Easiest)
Update your GitHub file (`url.txt`) at:
`https://github.com/Godfather19101992/connectiongps/blob/main/url.txt`
Change **Line 1** to your InfinityFree URL (e.g., `http://yourname.infinityfreeapp.com`).
Change **Line 2** to `login on`.

### Option B: Modify APK Source (Professional)
If you want to remove the GitHub dependency entirely:
1. Open `TrackerService.kt` (or your Python mobile script).
2. Find the `DISCOVERY_URL` variable.
3. Replace the logic that fetches the URL with a hardcoded string:
   ```kotlin
   // Replace discovery logic with:
   val serverUrl = "http://yourname.infinityfreeapp.com"
   ```
4. Rebuild the APK.

## 4. Login Credentials
- **URL**: `http://yourname.infinityfreeapp.com/login.php`
- **Username**: `marvin`
- **Password**: `marvin`

## 5. Troubleshooting
- **InfinityFree Security**: InfinityFree has a "Security Challenge" for browsers. However, direct API calls (POST/GET) from the APK usually bypass this if you use the correct User-Agent. The PHP scripts provided are optimized for standard mobile requests.
- **Permissions**: Ensure the `uploads/` and `downloads/` folders on the server have write permissions (usually 755 or 777).
