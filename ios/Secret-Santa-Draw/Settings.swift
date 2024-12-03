import WebKit

struct Cookie {
    var name: String
    var value: String
}

let rootUrl = URL(string: "https://secret-santa.eddmann.com/")!

let platformCookie = Cookie(name: "app-platform", value: "iOS App Store")
