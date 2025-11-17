# AGENTS.md

This file provides guidance to AI coding agents when working with code in this repository.

## Project Overview

The iOS app is a **thin native wrapper** around the Secret Santa Draw PWA, providing:
- Native iOS app experience with WKWebView
- Universal Link support for deep linking
- Custom URL scheme handling
- Platform detection via cookies
- Loading progress and connection retry

Built on the [ios-pwa-wrap](https://github.com/khmyznikov/ios-pwa-wrap) foundation with customizations for this project.

## Xcode Project Structure

### Project File

**Location**: `Secret-Santa-Draw.xcodeproj`

**Bundle Details**:
- Bundle Identifier: `com.eddmann.secret-santa`
- Development Team: `ANGUD7343N`
- Minimum iOS Version: Check scheme configuration

### Source Files

**Location**: `Secret-Santa-Draw/`

| File | Purpose |
|------|---------|
| `AppDelegate.swift` | Application lifecycle (minimal, UI-free) |
| `SceneDelegate.swift` | Scene management, deep link handling |
| `ViewController.swift` | Main UI controller with WebView |
| `WebView.swift` | WebView factory and configuration utilities |
| `Settings.swift` | Root URL and platform cookie config |
| `Base.lproj/Main.storyboard` | UI layout (WebView + loading) |
| `Base.lproj/LaunchScreen.storyboard` | Launch screen |
| `Secret-Santa-Draw.entitlements` | App capabilities |
| `Info.plist` | App configuration |

## Configuration Management

### Root URL Configuration

**Location**: `Settings.swift`

```swift
let rootUrl = URL(string: "https://secret-santa.eddmann.com/")!
```

**To update**:
1. Open `Settings.swift`
2. Modify `rootUrl` variable
3. Rebuild app

Used in `ViewController.loadUrl()` as the default launch URL.

### Platform Cookie Configuration

**Location**: `Settings.swift`

```swift
let platformCookie = Cookie(name: "app-platform", value: "iOS App Store")
```

**Purpose**: Identifies iOS app vs web browser to the server

**Cookie Details**:
- Name: `app-platform`
- Value: `iOS App Store`
- Expires: 1 year (31556926 seconds)
- Injected in `WebView.setCustomCookie()` function

**To update**:
1. Modify `platformCookie` in `Settings.swift`
2. Rebuild app

## WebView Setup and Customization

### WebView Factory

**Location**: `WebView.swift` `createWebView()` function

```swift
func createWebView(container: UIView, WKND: WKNavigationDelegate, NSO: NSObject, VC: ViewController) -> WKWebView {
    let config = WKWebViewConfiguration()

    // App-bound domains for security
    config.limitsNavigationsToAppBoundDomains = true

    // Media playback
    config.allowsInlineMediaPlayback = true

    // JavaScript window opening
    config.preferences.javaScriptCanOpenWindowsAutomatically = true

    // Standalone mode (PWA-like)
    config.preferences.setValue(true, forKey: "standalone")

    let webView = WKWebView(frame: .zero, configuration: config)

    // Navigation delegate
    webView.navigationDelegate = WKND

    // Custom User-Agent with PWAShell identifier
    webView.customUserAgent = "Mozilla/5.0 ... PWAShell"

    // Inspector for debugging (DEBUG builds only)
    #if DEBUG
    if #available(iOS 16.4, *) {
        webView.isInspectable = true
    }
    #endif

    // Gestures and scrolling
    webView.allowsBackForwardNavigationGestures = true
    webView.scrollView.bounces = false
    webView.scrollView.contentInsetAdjustmentBehavior = .never

    return webView
}
```

### Key Settings

- **App-bound domains**: Restricts WKWebView to configured domains
- **Inline media**: Allows videos to play inline
- **JavaScript windows**: PWA popup support
- **Standalone mode**: Hides Safari UI elements
- **No bounce**: Disables scroll bounce effect
- **Back/forward gestures**: Native swipe navigation

### Custom User-Agent

Format includes device model, OS version, and "PWAShell" identifier:
```
Mozilla/5.0 (device; CPU device OS version like Mac OS X) ... PWAShell
```

## Universal Link Configuration and Testing

### Configuration Files

**Entitlements** (`Secret-Santa-Draw.entitlements`):
```xml
<key>com.apple.developer.associated-domains</key>
<array>
    <string>applinks:secret-santa.eddmann.com</string>
</array>
```

**Info.plist**:
```xml
<key>WKAppBoundDomains</key>
<array>
    <string>secret-santa.eddmann.com</string>
</array>
```

### Server-side Requirements

The domain must host `.well-known/apple-app-site-association`:
```json
{
  "applinks": {
    "apps": [],
    "details": [
      {
        "appID": "TEAMID.com.eddmann.secret-santa",
        "paths": ["*"]
      }
    ]
  }
}
```

### Testing Universal Links

**From Simulator/Device**:
1. Open Safari (not from clipboard)
2. Navigate to `https://secret-santa.eddmann.com/remote/draws/123`
3. Long-press link → "Open in Secret Santa Draw"

**Debug**:
- Settings → Privacy & Security → Downloaded App Status
- Check if associated domains are verified

**Testing Tips**:
- Links must be tapped in Safari or Messages
- Pasted URLs don't trigger Universal Links
- Domain must be HTTPS and have valid SSL

## Deep Link Handling Patterns

### SceneDelegate Overview

**Location**: `SceneDelegate.swift`

Handles three types of deep links:
1. **Universal Links** (https://)
2. **Custom URL Schemes** (customscheme://)
3. **App Shortcuts**

### Universal Links

**App Launch** (`willConnectTo` method):
```swift
func scene(_ scene: UIScene, willConnectTo session: UISceneSession, options connectionOptions: UIScene.ConnectionOptions) {
    for userActivity in connectionOptions.userActivities {
        if let universalLink = userActivity.webpageURL {
            SceneDelegate.launchLinkUrl = universalLink
            return
        }
    }
}
```

**App Already Running** (`continue userActivity` method):
```swift
func scene(_ scene: UIScene, continue userActivity: NSUserActivity) {
    guard userActivity.activityType == NSUserActivityTypeBrowsingWeb,
        let universalLink = userActivity.webpageURL else {
        return
    }

    Secret_Santa_Draw.webView.evaluateJavaScript("location.href = '\(universalLink)'")
}
```

### Custom URL Schemes

**Pattern**: Convert custom scheme to HTTPS

**App Launch**:
```swift
if let schemeUrl = connectionOptions.urlContexts.first?.url {
    var comps = URLComponents(url: schemeUrl, resolvingAgainstBaseURL: false)
    comps?.scheme = "https"

    if let url = comps?.url {
        SceneDelegate.launchLinkUrl = url
    }
}
```

**App Running** (`openURLContexts` method):
```swift
func scene(_ scene: UIScene, openURLContexts URLContexts: Set<UIOpenURLContext>) {
    if let scheme = URLContexts.first?.url {
        var comps = URLComponents(url: scheme, resolvingAgainstBaseURL: false)
        comps?.scheme = "https"

        if let url = comps?.url {
            Secret_Santa_Draw.webView.evaluateJavaScript("location.href = '\(url)'")
        }
    }
}
```

### App Shortcuts

**App Launch**:
```swift
if let shortcutUrl = connectionOptions.shortcutItem?.type {
    SceneDelegate.launchLinkUrl = URL.init(string: shortcutUrl)
    return
}
```

**App Running** (`performActionFor` method):
```swift
func windowScene(_ windowScene: UIWindowScene, performActionFor shortcutItem: UIApplicationShortcutItem, completionHandler: @escaping (Bool) -> Void) {
    if let shortcutUrl = URL.init(string: shortcutItem.type) {
        Secret_Santa_Draw.webView.evaluateJavaScript("location.href = '\(shortcutUrl)'")
    }
}
```

### Launch Link Storage

Deep links captured at launch are stored in static variable:
```swift
static var launchLinkUrl: URL? = nil
```

Used in `ViewController.loadUrl()`:
```swift
Secret_Santa_Draw.webView.load(URLRequest(url: SceneDelegate.launchLinkUrl ?? rootUrl))
```

## How to Build and Run the App

### Requirements

- Xcode 16.1+ (check scheme `LastUpgradeCheck`)
- macOS with Apple Silicon or Intel
- Apple Developer account for signing
- Development team configured

### Building in Xcode

1. Open `Secret-Santa-Draw.xcodeproj` in Xcode
2. Select scheme: **Secret-Santa-Draw**
3. Choose destination: iOS Simulator or Device
4. Build: `Product > Build` (⌘B)
5. Run: `Product > Run` (⌘R)

### Build Settings

- **Code Signing**: Automatic
- **Code Signing Identity**: Apple Development
- **Provisioning Profile**: Automatic
- **Entitlements**: `Secret-Santa-Draw.entitlements`

### Command Line Build

```bash
xcodebuild \
  -project Secret-Santa-Draw.xcodeproj \
  -scheme Secret-Santa-Draw \
  -configuration Debug \
  -destination 'platform=iOS Simulator,name=iPhone 15' \
  build
```

### Running on Simulator

```bash
xcodebuild \
  -project Secret-Santa-Draw.xcodeproj \
  -scheme Secret-Santa-Draw \
  -configuration Debug \
  -destination 'platform=iOS Simulator,name=iPhone 15' \
  test
```

### Running on Device

1. Connect iOS device via USB
2. Trust developer certificate on device
3. Select device in Xcode
4. Build and run (⌘R)

## Debugging WebView Issues

### Remote Inspector (iOS 16.4+)

**Enable** (DEBUG builds only):
```swift
#if DEBUG
if #available(iOS 16.4, *) {
    webView.isInspectable = true
}
#endif
```

**Access**:
1. Connect device or run simulator
2. Open Safari on Mac
3. Safari → Develop → [Device] → [App]
4. Inspect DOM, console, network

### Progress Monitoring

**KVO Observer** (`ViewController.swift`):
```swift
func initWebView() {
    Secret_Santa_Draw.webView.addObserver(
        self,
        forKeyPath: #keyPath(WKWebView.estimatedProgress),
        options: .new,
        context: nil
    )
}

override func observeValue(forKeyPath keyPath: String?, ...) {
    if keyPath == #keyPath(WKWebView.estimatedProgress) &&
       Secret_Santa_Draw.webView.isLoading {
        var progress = Float(Secret_Santa_Draw.webView.estimatedProgress)

        if progress >= 0.8 { progress = 1.0 }
        if progress >= 0.3 { self.animateConnectionProblem(false) }

        self.setProgress(progress, true)
    }
}

deinit {
    Secret_Santa_Draw.webView.removeObserver(
        self,
        forKeyPath: #keyPath(WKWebView.estimatedProgress)
    )
}
```

### Error Handling

**Connection Failures** (`didFailProvisionalNavigation`):
```swift
func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) {
    htmlIsLoaded = false

    // Ignore cancellations (-999)
    if (error as NSError)._code != (-999) {
        webView.isHidden = true
        loadingView.isHidden = false
        animateConnectionProblem(true)

        setProgress(0.05, true)

        // Auto-retry after 6 seconds
        DispatchQueue.main.asyncAfter(deadline: .now() + 3) {
            self.setProgress(0.1, true)
            DispatchQueue.main.asyncAfter(deadline: .now() + 3) {
                self.loadUrl()
            }
        }
    }
}
```

**UI Elements**:
- `progressView` - Loading progress bar
- `connectionProblemView` - Animated WiFi error icon
- `loadingView` - Splash screen container

### Navigation Policy Debugging

**External Link Detection** (`decidePolicyFor`):
```swift
func webView(_ webView: WKWebView, decidePolicyFor navigationAction: WKNavigationAction, decisionHandler: @escaping (WKNavigationActionPolicy) -> Void) {
    guard let url = navigationAction.request.url else {
        decisionHandler(.allow)
        return
    }

    // Allow same-host navigation
    if let urlHost = url.host, let rootHost = rootUrl.host, urlHost == rootHost {
        decisionHandler(.allow)
        return
    }

    // Open external HTTPS in SafariViewController
    if ["http", "https"].contains(url.scheme?.lowercased() ?? "") {
        self.present(SFSafariViewController(url: url), animated: true)
    } else if UIApplication.shared.canOpenURL(url) {
        UIApplication.shared.open(url)
    }

    decisionHandler(.cancel)
}
```

## App-Bound Domains Configuration

### Purpose

Security feature that restricts WKWebView capabilities to specific domains.

### Configuration

**WebView Config** (`WebView.swift`):
```swift
config.limitsNavigationsToAppBoundDomains = true
```

**Info.plist**:
```xml
<key>WKAppBoundDomains</key>
<array>
    <string>secret-santa.eddmann.com</string>
</array>
```

### Effects

- Disables certain JavaScript APIs outside app-bound domains
- Only HTTPS to bound domains in certain contexts
- External links open in Safari (see `decidePolicyFor`)

### Testing

Verify non-app-bound URLs open in SafariViewController:
1. Add link to external site in PWA
2. Tap link in app
3. Should open Safari, not in WebView

## Platform Detection and Native Features

### Platform Cookie

**Injection** (`WebView.swift` `setCustomCookie()`):
```swift
func setCustomCookie(webView: WKWebView, completion: (() -> Void)? = nil) {
    let cookie = HTTPCookie(properties: [
        .domain: rootUrl.host ?? "",
        .path: "/",
        .name: platformCookie.name,
        .value: platformCookie.value,
        .expires: Date().addingTimeInterval(31556926), // 1 year
        .secure: true,
    ])

    webView.configuration.websiteDataStore.httpCookieStore.setCookie(cookie!) {
        completion?()
    }
}
```

**Server Detection**:
```javascript
// In PWA
document.cookie.includes('app-platform=iOS App Store')
```

### Custom User-Agent

Format includes "PWAShell" identifier for detection:
```
Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) ... PWAShell
```

**Server Detection**:
```javascript
navigator.userAgent.includes('PWAShell')
```

### Native Features

**External Links** → SafariViewController (in-app browser)
**Custom Schemes** → Converted to HTTPS and navigated
**Back/Forward** → Native swipe gestures
**Deep Links** → Universal Links + custom schemes
**App Shortcuts** → Quick actions (if configured)

## ios-pwa-wrap Foundation

### Template Pattern

This app follows the ios-pwa-wrap pattern from [github.com/khmyznikov/ios-pwa-wrap](https://github.com/khmyznikov/ios-pwa-wrap):
- Thin native wrapper around web content
- WKWebView-based with minimal native code
- Navigation policy delegates external links
- Deep link support via Scene Delegate
- Customizable configuration

### Customizations Made

**1. Platform Cookie Injection** (`Settings.swift`, `WebView.swift`)
- Not standard in template
- Identifies iOS app vs web browser

**2. Custom User-Agent** (`WebView.swift`)
- "PWAShell" marker for detection
- Device and OS version included

**3. Connection Error Retry** (`ViewController.swift`)
- Animated error indicator (WiFi icon)
- Auto-retry with 3-second delays
- Progress tracking during retry

**4. Loading Progress UI** (`Main.storyboard`, `ViewController.swift`)
- Progress bar with KVO observation
- Splash screen during load
- Connection problem animation

**5. App-bound Domains** (`Info.plist`, `WebView.swift`)
- Security feature configuration
- Limits WebView to specific domain

**6. Remote Debugging** (`WebView.swift`)
- Inspector enabled in DEBUG builds (iOS 16.4+)
- Conditional compilation for release

## iOS-Specific Conventions in This Project

### Naming

**Module Prefix**:
- `Secret_Santa_Draw` class name (Swift naming quirk)
- Global `webView` variable at module level
- Storyboard: `$(PRODUCT_MODULE_NAME).SceneDelegate`

**Singleton Pattern**:
```swift
var webView: WKWebView! = nil

// Accessed globally
Secret_Santa_Draw.webView
```

### Architecture

**Minimal AppDelegate**:
- No UI code
- Modern scene-based approach

**SceneDelegate for Lifecycle**:
- All deep link handling
- Scene activation/deactivation
- Launch link storage

**Global WebView**:
- Singleton instance
- Shared across ViewController and SceneDelegate
- Prevents recreation on navigation

### UI

**Theme Color**: RGB(202, 5, 44) - Brand red matching PWA

**Safe Area Handling**:
```swift
func calcWebviewFrame(_ container: UIView) -> CGRect {
    let statusBarHeight = UIApplication.shared.statusBarFrame.height
    return CGRect(
        x: 0,
        y: statusBarHeight,
        width: container.frame.width,
        height: container.frame.height - statusBarHeight
    )
}
```

**Loading UI Elements** (Main.storyboard):
- `loadingView` - Container for splash
- `progressView` - UIProgressView
- `connectionProblemView` - Animated WiFi icon
- `webviewView` - WebView container

### Memory Management

**KVO Observer Cleanup**:
```swift
deinit {
    Secret_Santa_Draw.webView.removeObserver(
        self,
        forKeyPath: #keyPath(WKWebView.estimatedProgress)
    )
}
```

**Preconcurrency Imports** (Swift 6 compatibility):
```swift
@preconcurrency import WebKit
```

## File Paths Reference

### Key Files

- `Secret-Santa-Draw/AppDelegate.swift`
- `Secret-Santa-Draw/SceneDelegate.swift`
- `Secret-Santa-Draw/ViewController.swift`
- `Secret-Santa-Draw/WebView.swift`
- `Secret-Santa-Draw/Settings.swift`
- `Secret-Santa-Draw/Secret-Santa-Draw.entitlements`
- `Secret-Santa-Draw/Info.plist`
- `Secret-Santa-Draw/Base.lproj/Main.storyboard`
- `Secret-Santa-Draw.xcodeproj/project.pbxproj`
- `Secret-Santa-Draw.xcodeproj/xcshareddata/xcschemes/Secret-Santa-Draw.xcscheme`

## Common Code Examples

### Changing Root URL

**File**: `Settings.swift`
```swift
// Change this line
let rootUrl = URL(string: "https://your-domain.com/")!
```

### Adding Custom Cookie

**File**: `Settings.swift`
```swift
let customCookie = Cookie(name: "custom-key", value: "custom-value")
```

**File**: `WebView.swift` (in `setCustomCookie` function)
```swift
// Add additional cookie
let customCookie = HTTPCookie(properties: [
    .domain: rootUrl.host ?? "",
    .path: "/",
    .name: customCookie.name,
    .value: customCookie.value,
    .expires: Date().addingTimeInterval(31556926),
    .secure: true,
])
webView.configuration.websiteDataStore.httpCookieStore.setCookie(customCookie!)
```

### Testing Deep Links in Simulator

**Terminal Command**:
```bash
xcrun simctl openurl booted "https://secret-santa.eddmann.com/remote/draws/123"
```

### Debugging JavaScript

**In Remote Inspector Console**:
```javascript
// Check if in iOS app
console.log(document.cookie.includes('app-platform=iOS App Store'))
console.log(navigator.userAgent.includes('PWAShell'))

// Navigate programmatically
window.location.href = '/new-path'
```
