class ExtendWpSearch {
    constructor(options = {}) {
        // Default settings
        this.settings = Object.assign({
            searchTrigger: extend_wp_search_vars.trigger || '', // Set from extend_wp_search_vars.trigger
            formSelector: '#ewps-search-form',
            submitButton: '#submit',
            moreResultsButton: '#more-results-button', // Add more-results button
            resultsContainer: '#search-results',
            filtersContainer: '#search_form_filter',
            bodyClass: 'full-screen-open',
            fullScreenClass: 'full-screen-open-left',
            dataTrigger: 'data-trigger',
            inputLength: 3,
            pagination: extend_wp_search_vars.pagination || 'numbers', 
            liveSearchInterval: 249,
            normalFormSubmit: false // Flag to handle normal form submission
        }, options);

        this.typingTimer = null;
        this.initialize(); // Initialize everything
    }

    // Method to initialize all event listeners and expose global methods
    initialize() {
        this.liveSearch();
        this.autoTrigger();

        // Cache form, button, and more-results button to avoid multiple lookups
        const submitButton = document.querySelector(this.settings.submitButton);
        const searchTriggerElement = document.querySelector(this.settings.searchTrigger);

        // Event listener for the submit button
        if (submitButton) {
            submitButton.addEventListener('click', (e) => {
                // Prevent the default form submission only if normalFormSubmit is false
                if (!this.settings.normalFormSubmit) {
                    e.preventDefault();
                    this.search(); // Calling search method
                }
            });
        }



        // Check if a custom search trigger is provided in extend_wp_search_vars
        if (searchTriggerElement) {
            searchTriggerElement.addEventListener('click', () => {
                if (document.body.classList.contains('ewps-search-page-results')) {
                    window.scrollTo({ top: document.querySelector("#search_form").offsetTop - 200, behavior: 'smooth' });
                    return;
                }
                this.toggleFullScreen();
            });
        } else if (this.settings.searchTrigger !== '') {
            console.log(`Search trigger element not found: ${this.settings.searchTrigger}`);
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
        const filterTrigger = document.querySelector('#filter-trigger');
        if (document.querySelector(this.settings.filtersContainer).classList.contains('active')) {
            this.changeSearchContainer(filterTrigger);
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
        const container = fullScreen ? '#search-full-screen' : 'body.page';

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
        const input = document.querySelector('input[name="searchtext"]');
        /*check input lenth*/
        if (input.value.length < this.settings.inputLength) {
            return;
        }

        const form = document.querySelector(container + ' ' + this.settings.formSelector);
        if (!form) return;
        /*add pagination typeto data*/
        if (this.compareFormData(form)) {
            console.log("Query is the same, no action taken.");
            return; // Don't proceed with the search if the data is the same
        }
        this.setLoading(true);



        const formData = new FormData(form);
        const queryString = new URLSearchParams(formData).toString(); // Convert formData to query string
        // Fetch API with better error handling
        fetch(`${awmGlobals.url}/wp-json/extend-wp-search/search/?${queryString}`, {
            method: 'GET',
            cache: 'no-cache'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network error: ${response.statusText}`);
                }
                return response.text();
            })
            .then(data => {
                this.setLoading(false);
                var html_data = JSON.parse(data);
                const resultsContainer = document.querySelector(container + ' ' + this.settings.resultsContainer);

                if (this.settings.pagination === 'button') {
                    const resultsWrapper = resultsContainer.querySelector('.results-wrapper');
                    if (resultsWrapper && document.querySelector('#ewps-search-form input[name="paged"]').value > 1) {
                        resultsWrapper.innerHTML += html_data.results;
                        if (html_data.button != '') {
                            document.querySelector('.ewps-pagination').innerHTML = html_data.button;
                        }

                    }
                    else {
                        resultsContainer.innerHTML = html_data;
                    }
                } else {
                    // Replace content for pagination
                    resultsContainer.innerHTML = html_data;
                }
                // Initialize pagination or infinite scroll
                this.initPagination();
                this.checkFullScreenButton();
                // Optionally trigger any custom event after search results are rendered
                document.dispatchEvent(new Event('extend_wp_search_results'));

            })
            .catch(error => {
                this.setLoading(false);
                console.error('Search error:', error);
            });
    }


    // Method to serialize form data and compare with previous data
    compareFormData(form) {
        // Serialize the current form data into a query string format
        const formData = new FormData(form);
        let hasNonPagedChanges = false;
        const currentQuery = new URLSearchParams(formData);
        const currentQueryString = currentQuery.toString();
        // Parse the previous query string into a URLSearchParams object
        const previousQuery = this.settings.previousQueryString
            ? new URLSearchParams(this.settings.previousQueryString)
            : new URLSearchParams();

        // Iterate over current form data and compare with previous data
        for (const [key, value] of currentQuery.entries()) {
            if (key !== 'paged') {
                // If any field other than 'paged' has changed, mark it
                if (value !== previousQuery.get(key)) {
                    hasNonPagedChanges = true;
                    break; // We can exit early if we detect a change
                }
            }
        }

        // If there are changes in any field except 'paged', reset 'paged' to 1
        if (hasNonPagedChanges) {
            form.querySelector('input[name="paged"]').value = 1;
        }


        // Check if there is previous form data stored
        if (this.settings.previousQueryString) {
            // Compare the new form data with the old data (excluding the 'paged' parameter)
            const isSameQuery = currentQueryString === this.settings.previousQueryString

            // If the data is the same, return true, meaning no need to run the search
            if (isSameQuery) {
                return true;
            }
        }

        // Update the stored previousQueryString with the new data
        this.settings.previousQueryString = currentQueryString;

        // Return false, meaning the data has changed and the search should be executed
        return false;
    }


    // Method to check if the full screen button is visible
    checkFullScreenButton() {
        // Add event listener for dynamically added #more-results-button
        const moreResultsButton = document.querySelector(this.settings.moreResultsButton);
        // Event listener for the "more-results-button" click event
        if (moreResultsButton) {
            moreResultsButton.addEventListener('click', () => {
                const submitButton = document.querySelector(this.settings.submitButton);
                // Allow normal form submission when moreResultsButton is clicked
                if (submitButton) {
                    this.settings.normalFormSubmit = true;
                    submitButton.click();
                }
            });
        }
    }

    // Method to toggle loading state
    setLoading(isLoading) {
        let loadingDiv = document.querySelector(this.settings.resultsContainer);
        if (this.settings.pagination === 'button' && document.querySelector('#ewps-search-form input[name="paged"]').value > 1) {
            const resultsWrapper = loadingDiv.querySelector('.ewps-pagination');
            if (resultsWrapper) {
                loadingDiv = resultsWrapper;
            }
        }
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


    // Initialize pagination or infinite scroll
    initPagination() {
        if (this.settings.pagination === 'button') {
            this.attachLoadMoreEvents();
        } else {
            this.attachPaginationEvents();
        }
    }

    // Attach click events to load more button
    attachLoadMoreEvents() {
        const loadMoreButton = document.querySelector('.ewps-load-more');
        if (loadMoreButton) {
            loadMoreButton.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(loadMoreButton.getAttribute('data-page')) + 1;
                loadMoreButton.setAttribute('data-page', page);
                document.querySelector('#ewps-search-form input[name="paged"]').value = page;
                this.search();

            });
        }
    }


    // Attach click events to pagination links
    attachPaginationEvents() {
        const paginationLinks = document.querySelectorAll('.ewps-pagination a.page-numbers');
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.innerText) || parseInt(link.getAttribute('href').split('page/')[1]);
                document.querySelector('#ewps-search-form input[name="paged"]').value = page;
                this.search();
            });
        });
    }


    // Handle infinite scroll (auto-load next page)
    handleInfiniteScroll() {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            const loadMoreButton = document.querySelector('.ewps-load-more');
            if (loadMoreButton) {
                const page = parseInt(loadMoreButton.getAttribute('data-page')) + 1;
                document.querySelector('#ewps-search-form input[name="paged"]').value = page;
                this.search();

            }
        }
    }

}
// Usage Example:
document.addEventListener('DOMContentLoaded', () => {
    const wpSearch = ExtendWpSearch.create({
        liveSearchInterval: 300
    });
});