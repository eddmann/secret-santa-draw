import UIKit

class SceneDelegate: UIResponder, UIWindowSceneDelegate {

    var window: UIWindow?
    
    static var launchLinkUrl: URL? = nil

    // App launched
    func scene(_ scene: UIScene, willConnectTo session: UISceneSession, options connectionOptions: UIScene.ConnectionOptions) {
        for userActivity in connectionOptions.userActivities {
            if let universalLink = userActivity.webpageURL {
                SceneDelegate.launchLinkUrl = universalLink
                return
            }
        }

        if let shortcutUrl = connectionOptions.shortcutItem?.type {
            SceneDelegate.launchLinkUrl = URL.init(string: shortcutUrl)
            return
        }

        if let schemeUrl = connectionOptions.urlContexts.first?.url {
            // Convert scheme://url to a https://url
            var comps = URLComponents(url: schemeUrl, resolvingAgainstBaseURL: false)
            comps?.scheme = "https"

            if let url = comps?.url {
                SceneDelegate.launchLinkUrl = url;
            }
        }
    }
    
    // App already running, universal link clicked
    func scene(_ scene: UIScene, continue userActivity: NSUserActivity) {
        guard userActivity.activityType == NSUserActivityTypeBrowsingWeb,
            let universalLink = userActivity.webpageURL else {
            return
        }
        
        Secret_Santa_Draw.webView.evaluateJavaScript("location.href = '\(universalLink)'")
    }
    
    // App already running, shortcut clicked
    func windowScene(_ windowScene: UIWindowScene, performActionFor shortcutItem: UIApplicationShortcutItem, completionHandler: @escaping (Bool) -> Void) {
        if let shortcutUrl = URL.init(string: shortcutItem.type) {
            Secret_Santa_Draw.webView.evaluateJavaScript("location.href = '\(shortcutUrl)'");
        }
    }
    
    // App already running, custom scheme URL clicked
    func scene(_ scene: UIScene, openURLContexts URLContexts: Set<UIOpenURLContext>) {
        if let scheme = URLContexts.first?.url {
            // Convert scheme://url to a https://url and navigate to it
            var comps = URLComponents(url: scheme, resolvingAgainstBaseURL: false)
            comps?.scheme = "https"

            if let url = comps?.url {
                Secret_Santa_Draw.webView.evaluateJavaScript("location.href = '\(url)'")
            }
        }
    }

    func sceneDidDisconnect(_ scene: UIScene) {
        // Called as the scene is being released by the system.
        // This occurs shortly after the scene enters the background, or when its session is discarded.
        // Release any resources associated with this scene that can be re-created the next time the scene connects.
        // The scene may re-connect later, as its session was not necessarily discarded (see `application:didDiscardSceneSessions` instead).
    }

    func sceneDidBecomeActive(_ scene: UIScene) {
        // Called when the scene has moved from an inactive state to an active state.
        // Use this method to restart any tasks that were paused (or not yet started) when the scene was inactive.
    }

    func sceneWillResignActive(_ scene: UIScene) {
        // Called when the scene will move from an active state to an inactive state.
        // This may occur due to temporary interruptions (ex. an incoming phone call).
    }

    func sceneWillEnterForeground(_ scene: UIScene) {
        // Called as the scene transitions from the background to the foreground.
        // Use this method to undo the changes made on entering the background.
    }

    func sceneDidEnterBackground(_ scene: UIScene) {
        // Called as the scene transitions from the foreground to the background.
        // Use this method to save data, release shared resources, and store enough scene-specific state information
        // to restore the scene back to its current state.
    }

}
