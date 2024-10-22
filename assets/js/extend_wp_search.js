class ExtendWpSearch {
    constructor(options = {}) {
        // Default settings
        this.settings = Object.assign({
            searchTrigger: extend_wp_search_vars.trigger || '', // Set from extend_wp_search_vars.trigger
            formSelector: '#ewps-search-form',
            submitButton: '#submit',
            resultsContainer: '#search-results',
            filtersContainer: '#search_form_filter',
            bodyClass: 'full-screen-open',
            fullScreenClass: 'full-screen-open-left',
            dataTrigger: 'data-trigger',
            liveSearchInterval: 249
        }, options);

        this.typingTimer = null;
        this.initialize(); // Initialize everything
    }

    // Method to initialize all event listeners and expose global methods
    initialize() {
        this.liveSearch();
        this.autoTrigger();

        // Ensure the submit button exists before adding event listener
        const submitButton = document.querySelector(this.settings.submitButton);
        if (submitButton) {
            submitButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.search(); // Calling search method
            });
        }

        // Check if a custom search trigger is provided in extend_wp_search_vars
        if (this.settings.searchTrigger !== '') {
            const searchTriggerElement = document.querySelector(this.settings.searchTrigger);

            if (searchTriggerElement) {
                searchTriggerElement.addEventListener('click', () => {
                    if (document.body.classList.contains('ewps-search-page-results')) {
                        window.scrollTo({ top: document.querySelector("#search_form").offsetTop - 200, behavior: 'smooth' });
                        return;
                    }
                    this.toggleFullScreen();
                });
            } else {
                console.log(`Search trigger element not found: ${this.settings.searchTrigger}`);
            }
        } else {
            console.error(`extend_wp_search_vars.trigger is empty.`);
        }

        // Expose methods to the global scope for inline `onclick`
        this.exposeGlobalMethods();
    }

    // Method to expose the required methods to the global scope
    exposeGlobalMethods() {
        window.extend_wp_search_click = this.search.bind(this); // Bind search method
        window.changeSearchContainer = this.changeSearchContainer.bind(this);
        window.extend_wp_search_close_search = this.extend_wp_search_close_search.bind(this);
        window.newSearch = this.newSearch.bind(this);
        window.disableCheckboxes = this.disableCheckboxes.bind(this);
    }

    // Method to trigger the search (can be called globally now)
    search() {
        if (document.querySelector(this.settings.filtersContainer).classList.contains('active')) {
            this.changeSearchContainer(document.querySelector('#filter-trigger'));
        }
        this.searchQuery();
    }

    // Method to handle live search functionality
    liveSearch() {
        const input = document.querySelector('input[name="searchtext"]');

        if (!input) return;

        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.search(); // Calling search method
            }
        });

        input.addEventListener('keyup', () => {
            clearTimeout(this.typingTimer);
            this.typingTimer = setTimeout(() => this.search(), this.settings.liveSearchInterval);
        });

        input.addEventListener('keydown', () => {
            clearTimeout(this.typingTimer);
        });
    }

    // Method to change search container (can be called globally)
    changeSearchContainer(wrap) {
        const fullScreen = document.body.classList.contains(this.settings.bodyClass);
        let container = fullScreen ? '#search-full-screen' : 'body.page';

        // Get the elements for results and filter
        const searchResults = document.querySelector(container + ' #search_form_resutls');
        const searchFormFilter = document.querySelector(container + ' #search_form_filter');

        // Toggle the active class between searchResults and searchFormFilter
        if (searchResults.classList.contains('active')) {
            searchResults.classList.remove('active');
            searchFormFilter.classList.add('active');
        } else {
            searchFormFilter.classList.remove('active');
            searchResults.classList.add('active');
        }
    }

    // Method to close the search screen (can be called globally)
    extend_wp_search_close_search() {
        document.body.classList.toggle(this.settings.bodyClass);
        document.body.classList.toggle(this.settings.fullScreenClass);
    }

    // Method to trigger new search (can be called globally)
    newSearch() {
        this.search(); // Calling search method
    }

    // Method to disable checkboxes (can be called globally)
    disableCheckboxes() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        this.search(); // Calling search method
    }

    // Method to trigger search automatically
    autoTrigger() {
        const searchForm = document.querySelector('#search_form');
        if (searchForm && searchForm.getAttribute(this.settings.dataTrigger) === '1') {
            this.search(); // Calling search method
        }
    }

    // Method to execute the search query
    searchQuery() {
        const container = document.body.classList.contains(this.settings.bodyClass) ? '#search-full-screen' : 'body.page';
        this.setLoading(true);

        const form = document.querySelector(container + ' ' + this.settings.formSelector);
        if (!form) return;

        const formData = new FormData(form);
        const queryString = new URLSearchParams(formData).toString(); // Convert formData to query string

        fetch(`${awmGlobals.url}/wp-json/extend-wp-search/search/?${queryString}`, {
            method: 'GET',
            cache: 'no-cache'
        })
            .then(response => response.text())
            .then(data => {
                this.setLoading(false);

                // Insert the response as HTML
                document.querySelector(container + ' ' + this.settings.resultsContainer).innerHTML = JSON.parse(data);

                // Optionally trigger any custom event after search results are rendered
                document.dispatchEvent(new Event('extend_wp_search_results'));
            })
            .catch(error => console.error('Search error:', error));
    }

    // Method to toggle loading state
    setLoading(isLoading) {
        const loadingDiv = document.querySelector(this.settings.resultsContainer);
        if (!loadingDiv) return;

        if (isLoading) {
            loadingDiv.classList.add('ewps-on-load');
            const loadingHtml = document.querySelector("#ewps-loading") ? document.querySelector("#ewps-loading").innerHTML : '';
            loadingDiv.innerHTML = loadingHtml;
        } else {
            loadingDiv.classList.remove('ewps-on-load');
            const loadingWrapper = loadingDiv.querySelector(".loading-wrapper");
            if (loadingWrapper) {
                loadingWrapper.style.display = 'none';
            }
        }
    }

    // Method to toggle full screen
    toggleFullScreen() {
        document.body.classList.toggle(this.settings.bodyClass);
        document.body.classList.toggle(this.settings.fullScreenClass);
    }

    // Method to create a new instance of the class
    static create(options) {
        return new ExtendWpSearch(options);
    }

    // Method to update settings dynamically
    update(newSettings) {
        this.settings = Object.assign(this.settings, newSettings);
    }

    // Method to destroy the instance
    destroy() {
        // Remove all event listeners, clear timeouts
        clearTimeout(this.typingTimer);

        const submitButton = document.querySelector(this.settings.submitButton);
        if (submitButton) submitButton.removeEventListener('click', this.search);

        const searchTrigger = document.querySelector(this.settings.searchTrigger);
        if (searchTrigger) searchTrigger.removeEventListener('click', this.toggleFullScreen);
    }
}

// Usage Example:
document.addEventListener('DOMContentLoaded', () => {
    const wpSearch = ExtendWpSearch.create({
        liveSearchInterval: 300
    });
});