# Search Interface using Extend WP

A simple UI interface to create a search engine for WordPress.

## Installation

1. **Install the plugin**: Follow the usual WordPress plugin installation process.
   
2. **Create a search results page**:
   - Create a new page where you want the search results to be displayed.
   - Add the custom block "**Extend WP Search Interface**".
   - Set "Show Results" to **true** in the block settings.

3. **Configure the search interface**:
   - Navigate to **Extend WP** > **Search Interface** in the WordPress admin.
   - Here, you can adjust the following settings:
     - **Element class/id to trigger search on click**: Enter a valid query selector, like `#main` or `.class`, to trigger the search on click.
     - **Search Results Page ID**: Enter the page ID where the search results will be displayed.
     - **Post Types to Search In**: Select the post types you want to include in the search.
     - **Years**: If you want to limit the search by years, enter them here (comma-separated).
     - **Default Featured Image**: Set the image to be shown if no featured image is available.
     - **Taxonomies to Filter**: Select the taxonomies you'd like to use as filters.
     - **Taxonomies to Exclude**: Enter the IDs of the taxonomies you'd like to exclude from the search.
     - **Default Order Type**: Choose how search results should be ordered (e.g., by publish date, alphabetical order, etc.).
     - **Default Results Limit**: Set the number of results to display per page.
     - **Pagination Types**: Choose between pagination options like "Button" or "Infinite Scroll".
     - **Include Scripts**: Optionally, enter the IDs of pages where you want to include the search functionality. If left empty, the script will be included globally.

4. **Overwriting templates**:
   - If you'd like to customize the templates, copy the `templates` folder from the plugin directory to your active theme.
   - Place it under `templates/ewp-search/` in your theme, and you can modify the templates as needed.

5. **Search Bar Without Filters or Results**:
   - To include just the search bar without filters or results display:
     - Add the custom block "**Extend WP Search Interface**" to your page.
     - Set both "Show Filters" and "Show Results" to **false** in the block settings.

6. **Trigger Full-Screen Search**:
   - To trigger the search interface as a full-screen overlay, use the **Element Class/ID to Trigger Search on Click** option.
   - Enter a valid query selector (e.g., `#main`, `.class`) to trigger the full-screen search interface when the specified element is clicked.