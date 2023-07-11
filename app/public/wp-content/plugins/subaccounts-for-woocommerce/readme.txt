=== Subaccounts for WooCommerce ===
Contributors: mediaticus
Tags: subaccount, multi user, multiple accounts, woocommerce, user switching, sub account, company accounts, parent account, child account, woocommerce b2b, switch user, account
Tested up to: 6.2
Requires PHP: 5.7
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Subaccounts for WooCommerce allows the creation of sub-accounts for your WooCommerce customers and subscribers.

== Description ==

Subaccounts for WooCommerce allows the creation of subaccounts for your WooCommerce customers and subscribers.

The ability to create multiple subaccounts tied to a main parent account is extremely useful in all of the following usage scenarios:

* companies that need to add and manage accounts for their employees
* stores that need to place orders from different branches
* businesses with multiple decision-makers for purchases
* when parent accounts need to supervise and monitor all the activities made by their own sub-accounts (purchases, expenses, quotes, etc.) and even place orders on behalf of them;
* in the case of company accounts that need to place orders from different purchasing departments or through multiple purchasing managers (B2B and similar);
* in those situations where a sales agent or sales representative needs to create new accounts for newly acquired customers and, if desired, place orders on their behalf.

### Main features

Subaccounts for WooCommerce (free version) provides support for the following features:

#### My Account area

* Possibility for customers to create/add new subaccounts (up to 10 subaccounts for each parent account in the free version).
* Possibility for a parent account to view subaccount orders directly from its *My Account* area.
* Ability for a parent account to switch account to its subaccounts using our advanced and secure user switching system.
* Ability for a parent account to monitor purchases made by its subaccounts and even place orders on their behalf.
* For each purchase, display information about who did actually placed the order (whether it was placed by a parent account on behalf of a subaccount or by a subaccount itself).

#### Admin area

* Customize the appearance of the *User Switcher Pane* to match the color scheme of your theme.
* Possibility to limit the number of subaccounts that a parent account can create/add.
* Possibility to choose user roles (including custom ones) to enable the subaccount system for.
* In WooCommerce order page, for each order, display information about customer's account type (whether a parent account or a subaccount).
* In case of purchase made by a subaccount, for each order display information about the subaccount's parent account, so that you know which user belongs to whom.

### Premium features

**Subaccounts Pro** (premium version) is an extended version of the plugin and includes the following additional features:

* Each parent account (Manager) can create an unlimited number of subaccounts.
* Create, set and fully manage subaccounts directly from backend (as an administrator).
* Possibility to force subaccounts to inherit the billing/shipping address from their Manager.
* On admin side, in both WooCommerce orders list and WooCommerce order pages display information about who did actually placed an order (whether it was placed by a parent account on behalf of a subaccount or by a subaccount itself).
* Possibility to show parent accounts and subaccounts information in WooCommerce orders list page.
* Possibility to show parent accounts and subaccounts information in WordPress users list page.
* On WordPress users list page, filter customers and subscribers based on account type (e.g. show only parent accounts or subaccounts).
* Possibility to include parent account and sub-account information related to an order in WooCommerce new order emails (for either admin or customers, or both).

Upgrade to *Subaccounts Pro* and get a full suite of tools to create and manage subaccounts in WooCommerce! 

Upgrading to **Subaccounts Pro** is easy: just install the free version of the plugin and navigate to: **Admin Dashboard > WooCommerce > Subaccounts > Add-Ons**. From there you will be able to easily and securely upgrade to the premium version of the plugin in just a few clicks.

### Languages and translations

**Subaccounts for WooCommerce** is translation ready and is currently available in the following languages:

* English
* Italian

More translations soon available.

== Installation ==

### Automatic installation
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of Subaccounts for WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "Subaccounts for WooCommerce" and click Search Plugins. You can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation. After installation has finished, click the 'activate plugin' link.

### Manual installation via the WordPress interface
1. Download the plugin zip file to your computer
2. Go to the WordPress admin panel menu Plugins > Add New
3. Choose upload
4. Upload the plugin zip file, the plugin will now be installed
5. After installation has finished, click the 'activate plugin' link

### Manual installation via FTP
1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.


== Frequently Asked Questions ==

= What is a subaccount? =

A subaccount (or sub-account) is a secondary account that sits under a main parent account.

= What is a Manager? =

A **Manager** is a parent account.

= How many subaccounts can be created for each customer/subscriber? =

Subaccounts for WooCommerce (free version) gives you the possibility to create up to 10 subaccounts for each Manager (parent account). 

With *Subaccounts Pro* (premium version) each Manager can have an unlimited number of subaccounts.

= Can a parent account (Manager) place an order? =

Both parent accounts and subaccounts can place orders. Parent accounts can also place orders on behalf of subaccounts, while a subaccount can place orders only for itself.

= Is it possible to create multiple nested subaccounts (e.g. a subaccount of another subaccount)? =

Only the creation of two account levels is allowed: a main parent account (so called Manager) and one or more subaccounts tied to the parent account.

With **Supervisor Add-On** it is possible to set a parent account (Supervisor) above a Manager account type, thus allowing you (as an administrator) to create a three-level structure of nested accounts.

= Is it possible for an Administrator to create and set a subaccount for a customer from WordPress backend (admin dashboard)? =

This option, along with many others, is only available in *Subaccounts Pro*, the premium version of the plugin.

= Does the plugin work on multisite installations? =

WordPress Multisite installations are not officially supported for now.

== Screenshots ==

1. Plugin Settings Page - Options Tab
2. Plugin Settings Page - Appearance Tab
3. Admin Order List Page (premium feature)
4. Admin Order Page
5. My Account - User Switcher
6. My Account - Subaccount Orders
7. My Account - Add Subaccount
8. My Account - Orders
9. Add/Manage Subaccount from Admin Side (premium feature)
10. Admin Users List Page (premium feature)


== Changelog ==

= 1.4.0 =
*Release Date July 6, 2023*

* **New** – Ability for a parent account to edit its subaccounts directly from its *My Account* area, within the newly introduced `Manage Subaccounts` page.
* **New** – Multi-User mode [Beta feature].
* **Improvement** – Minor code improvements.
* **Improvement** – Updated Freemius SDK to version 2.5.10.
* **Fix** – Fixed some user queries that prevented users with custom roles from being visible.
* **Fix** – Prevented users with custom roles from appearing if their role is disabled in the plugin settings.
* **Fix** – Call the `save()` method in function `sfwc_store_subaccounts_meta_data_on_order_creation`.

= 1.3.0 =
*Release Date May 25, 2023*

* **New** – Possibility to limit the number of subaccounts that a parent account can create/add.
* **New** – Possibility to choose user roles (including custom ones) to enable the subaccount system for.
* **New** – Possibility to force subaccounts to inherit the billing/shipping address from their Manager (Subaccounts Pro required).
* **Improvement** – Ability for the Manager to filter subaccounts present in the User Switcher dropdown menu.
* **Improvement** – Preserve Subaccount Orders search while selecting subaccount orders.
* **Improvement** – Updated Freemius SDK to version 2.5.8.
* **Tweak** – Moved `Options` tab before `Appearance` tab on plugin settings page.
* **Dev** – Added helper function: `sfwc_is_current_user_role_valid` to determine if current user role is eligible for the subaccount system.
* **Dev** – Added helper function: `sfwc_is_current_user_role_enabled` to determine if current user role is enabled from plugin settings.
* **Dev** – Added helper function: `sfwc_is_user_role_valid` to determine if user role is eligible for the subaccount system (by passing user ID).
* **Dev** – Added helper function: `sfwc_is_user_role_enabled` to determine if user role is enabled from plugin settings (by passing user ID).

= 1.2.1 =
*Release Date April 25, 2023*

* **Improvement** – Added `Account` column in Subaccount Orders page in front end.
* **Improvement** – Display `Account`, `Account type` and `Order placed by` information also in front-end order pages.
* **Improvement** – Minor CSS modifications in back-end users list to improve readability in case Subaccounts Pro is active.
* **Improvement** – Updated Freemius SDK to version 2.5.6.
* **Fix** – Honor `Customer Display Name` setting in "Order placed by" column in front end.
* **Tweak** – Minor text changes and corrections.

= 1.2.0 =
*Release Date March 20, 2023*

* **New** – Introduced support for the new WooCommerce High-Performance Order Storage (HPOS) feature.
* **Improvement** – Updated Freemius SDK to version 2.5.5.
* **Dev** – Replaced deprecated hook `woocommerce_my_account_my_orders_columns` with `woocommerce_account_orders_columns`.

= 1.1.4 =
*Release Date February 15, 2023*

* **Fix** – Restored function `sfwc_register_subaccounts_endpoint` to prevent endpoint returning `Not Found Error`.

= 1.1.3 =
*Release Date February 10, 2023*

* **New** – Added `Account` submenu item to show license activation status (`Subaccounts Pro` must be installed for the submenu item to appear).
* **Improvement** – Updated Freemius SDK to version 2.5.3.
* **Improvement** – Make sure the `session expired` notice appears in front end also if the parent account has switched to a subaccount.
* **Improvement** – Escaped some URLs before redirects are applied.
* **Fix** – Prevent `<ul>` tag from appearing twice in DOM on Subaccount Orders page in front end.
* **Fix** – Prevent showing the Subaccounts Order form in case the logged in Manager has no subaccount.
* **Fix** – Fixed undefined `wp_button_class` on Subaccount Orders page in frontend after form is submitted.
* **Tweak** – Corrected typo in function name `sfwc_insert_subaccounts_endpoint_content`.
* **Tweak** – Modified some texts in the `Options` page in back end.
* **Removed** – Removed function `sfwc_add_subaccounts_query_vars`.
* **Removed** – Removed function `sfwc_register_subaccounts_endpoint`.

= 1.1.2 =
*Release Date December 5, 2022*

* **Improvement** – Make the user switcher pane appear immediately after the first subaccount is created for new accounts.
* **Improvement** – Prevent displaying the user switcher pane when only no longer existing subaccounts are present (e.g. subaccount users deleted by admin).
* **Fix** – Fixed undefined `already_children_existing` on Add Subaccount page in front end.

= 1.1.1 =
*Release Date November 1, 2022*

* **Fix** – Fixed undefined `sfwc_option_display_name` on Subaccount Orders page in front end.

= 1.1.0 =
*Release Date October 5, 2022*

* **New** – Possibility for a parent account to view subaccount orders directly from its *My Account* area.
* **Improvement** – Enqueue front-end scripts and styles only on *My Account* area.
* **Improvement** – Front-end subaccounts section splitted up in tabs.
* **Improvement** – Added a notice in back end in case a permalink settings update is required after plugin updates.
* **Improvement** – Updated Freemius SDK to version 2.4.5.
* **Fix** – Fixed WooCommerce custom endpoint returning `Not Found Error` in some cases.
* **Dev** – Defined two new PHP constants: `SFWC_CURRENT_VERSION` and `SFWC_REQUIRES_PERMALINK_UPDATE`.
* **Dev** – Store current plugin release information in `wp_options` table.

= 1.0.0 =
*Release Date June 25, 2022*

* Initial release.