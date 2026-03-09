from playwright.sync_api import sync_playwright

def verify_login_branding():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        page.goto("http://localhost:8001/verify_login.php")
        # Let animations finish
        page.wait_for_timeout(1000)
        page.screenshot(path="verification/login_branding.png")
        browser.close()

if __name__ == "__main__":
    verify_login_branding()
