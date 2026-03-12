from playwright.sync_api import sync_playwright

def verify_landing_page():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        page.goto("http://localhost:8001/index.php")
        page.wait_for_timeout(1000)
        page.screenshot(path="verification/landing_page.png")
        browser.close()

if __name__ == "__main__":
    verify_landing_page()
