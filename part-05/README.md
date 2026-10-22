# Part 05 – How to Create a Custom Database Table in WordPress Plugin

📺 **Watch the video:** https://youtu.be/F-lJLTyk3BQ

---

## What You'll Learn

- Create a custom database table using WordPress
- Use `$wpdb` to work with the database
- Create a table with `dbDelta()`
- Run code when a plugin is activated using `register_activation_hook()`
- Insert sample notice records into the database
- Prevent duplicate sample data when the plugin is activated again

---

## How to Use This Code

1. Download or copy the `show-notice` folder from this episode.
2. Upload it to `wp-content/plugins/` on your WordPress site.
3. Go to **Plugins → Installed Plugins** and activate **Show Notice**.
   - Activating the plugin will automatically create the `wp_show_notices` table and insert sample data.
4. Check your database (e.g. via phpMyAdmin) to verify the table was created.

---

## Series Navigation

| | |
|---|---|
| ⬅️ Previous | [Part 04 – Create Custom Submenu Pages](../4/README.md) |
| ➡️ Next | [Part 06 – Create a Custom Admin Table with WP_List_Table](../6/README.md) |

📂 **Full series:** https://github.com/makitweb/show-notice-wordpress-plugin
