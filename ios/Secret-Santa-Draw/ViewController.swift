import UIKit
@preconcurrency import WebKit
import SafariServices

var webView: WKWebView! = nil

class ViewController: UIViewController, WKNavigationDelegate, WKUIDelegate {
    
    @IBOutlet weak var loadingView: UIView!
    @IBOutlet weak var progressView: UIProgressView!
    @IBOutlet weak var connectionProblemView: UIImageView!
    @IBOutlet weak var webviewView: UIView!
    
    var htmlIsLoaded = false;
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        initWebView()
        loadUrl()
    }
    
    override func viewDidLayoutSubviews() {
        super.viewDidLayoutSubviews()

        Secret_Santa_Draw.webView.frame = calcWebviewFrame(webviewView)
    }
    
    func initWebView() {
        Secret_Santa_Draw.webView = createWebView(container: webviewView, WKND: self, NSO: self, VC: self)
        webviewView.addSubview(Secret_Santa_Draw.webView);
        
        Secret_Santa_Draw.webView.uiDelegate = self;
        
        Secret_Santa_Draw.webView.addObserver(self, forKeyPath: #keyPath(WKWebView.estimatedProgress), options: .new, context: nil)
    }
    
    @objc func loadUrl() {
        Secret_Santa_Draw.webView.load(URLRequest(url: SceneDelegate.launchLinkUrl ?? rootUrl))
    }
    
    func webView(_ webView: WKWebView, decidePolicyFor navigationAction: WKNavigationAction, decisionHandler: @escaping (WKNavigationActionPolicy) -> Void) {
        guard let url = navigationAction.request.url else {
            decisionHandler(.allow)
            return
        }
        
        if let urlHost = url.host, let rootHost = rootUrl.host, urlHost == rootHost {
            decisionHandler(.allow)
            return
        }
    
        if ["http", "https"].contains(url.scheme?.lowercased() ?? "") {
            self.present(SFSafariViewController(url: url), animated: true, completion: nil)
        } else if (UIApplication.shared.canOpenURL(url)) {
            UIApplication.shared.open(url)
        }
 
        decisionHandler(.cancel)
    }
    
    func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
        htmlIsLoaded = true
        
        self.setProgress(1.0, true)
        self.animateConnectionProblem(false)
        
        DispatchQueue.main.asyncAfter(deadline: .now() + 0.8) {
            Secret_Santa_Draw.webView.isHidden = false
            self.loadingView.isHidden = true
           
            self.setProgress(0.0, false)
        }
    }
    
    func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) {
        htmlIsLoaded = false;
        
        if (error as NSError)._code != (-999) {
            webView.isHidden = true;
            loadingView.isHidden = false;
            animateConnectionProblem(true);
            
            setProgress(0.05, true);

            DispatchQueue.main.asyncAfter(deadline: .now() + 3) {
                self.setProgress(0.1, true);
                DispatchQueue.main.asyncAfter(deadline: .now() + 3) {
                    self.loadUrl();
                }
            }
        }
    }
    
    override func observeValue(forKeyPath keyPath: String?, of object: Any?, change: [NSKeyValueChangeKey : Any]?, context: UnsafeMutableRawPointer?) {

        if (keyPath == #keyPath(WKWebView.estimatedProgress) &&
            Secret_Santa_Draw.webView.isLoading &&
                !self.loadingView.isHidden &&
                !self.htmlIsLoaded) {
                    var progress = Float(Secret_Santa_Draw.webView.estimatedProgress);
                    
                    if (progress >= 0.8) { progress = 1.0; };
                    if (progress >= 0.3) { self.animateConnectionProblem(false); }
                    
                    self.setProgress(progress, true);
        }
    }
    
    func setProgress(_ progress: Float, _ animated: Bool) {
        self.progressView.setProgress(progress, animated: animated);
    }
    
    func animateConnectionProblem(_ show: Bool) {
        if (show) {
            self.connectionProblemView.isHidden = false;
            self.connectionProblemView.alpha = 0
            UIView.animate(withDuration: 0.7, delay: 0, options: [.repeat, .autoreverse], animations: {
                self.connectionProblemView.alpha = 1
            })
        }
        else {
            UIView.animate(withDuration: 0.3, delay: 0, options: [], animations: {
                self.connectionProblemView.alpha = 0 // Here you will get the animation you want
            }, completion: { _ in
                self.connectionProblemView.isHidden = true;
                self.connectionProblemView.layer.removeAllAnimations();
            })
        }
    }
        
    deinit {
        Secret_Santa_Draw.webView.removeObserver(self, forKeyPath: #keyPath(WKWebView.estimatedProgress))
    }
}
