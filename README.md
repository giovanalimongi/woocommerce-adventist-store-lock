# WooCommerce Adventist Store Lock

A lightweight WooCommerce snippet that blocks purchases weekly from **Friday 6:00 PM to Saturday 6:00 PM**, while also syncing the status of an **Elementor popup** to match the restricted period.

This solution was created for a real business rule: the store owner did not want purchases to be completed during a specific weekly time window for religious reasons.

## Overview

This snippet does two things:

- **Synchronizes an Elementor popup**  
  The popup is automatically set to `publish` during the restricted period and to `draft` outside it.

- **Blocks purchases in WooCommerce**  
  Even if a user bypasses the popup, the store still prevents:
  - product purchases
  - add-to-cart actions
  - cart and checkout progress

This creates a safer and more reliable solution than relying on a popup alone.

---

## Why this project matters

This is not just a UI automation.

It solves a **business rule with technical consistency**, separating:

- a **visual layer** (popup communication)
- a **functional layer** (purchase blocking in WooCommerce)

It was designed to work **without server cron access**, which is a common limitation in many real-world WordPress projects.

---

## Features

- Weekly recurring schedule:
  - **Friday from 18:00 onward**
  - **Saturday until 18:00**
- Uses the **WordPress timezone**
- Automatically changes Elementor popup status:
  - `publish` during the blocked period
  - `draft` outside the blocked period
- Prevents product purchases during the blocked period
- Blocks add-to-cart actions
- Removes purchase buttons in product and archive pages
- Displays notices in cart and checkout

---

## Requirements

- WordPress
- WooCommerce
- Elementor Pro
- An existing popup created in Elementor

---

## How it works

The snippet defines a central function that checks whether the current date/time falls inside the restricted weekly window.

That same function is reused to:

1. decide whether the popup should be published or drafted
2. decide whether WooCommerce purchases should be blocked

This avoids duplicated logic and keeps the business rule consistent across the site.

---

## Installation

1. Create your popup in **Elementor Pro**
2. Get the popup ID
3. Replace the popup ID inside the snippet
4. Add the snippet using:
   - **Code Snippets plugin**, or
   - your child theme’s `functions.php`
5. Test in a staging environment before pushing to production

---

## Usage

After installation:

- during the restricted period, the popup becomes **published**
- outside the restricted period, the popup becomes **draft**
- during the restricted period, WooCommerce purchases are disabled

---

## Notes

- The schedule uses the **timezone configured in WordPress**
- Since this solution does **not rely on server cron**, the popup status is synchronized on page load
- This means the change happens on the **first relevant request after the schedule boundary**
- For most storefronts, this is a practical solution with very low overhead

---

## Example use case

A store owner needed the website to remain visible during the Sabbath, but did not want customers to complete purchases during that time.

This solution allowed the store to:

- remain online
- communicate the temporary restriction with a popup
- technically enforce the purchase block in WooCommerce

---

## Possible improvements

Future versions could include:

- admin settings page
- customizable blocked days and hours
- support for multiple popups
- support for multiple schedules
- integration with server cron for exact timed execution
- plugin version with UI configuration

---

## File structure

```bash
woocommerce-adventist-store-lock/
├─ README.md
├─ snippet.php
├─ LICENSE
└─ screenshots/
