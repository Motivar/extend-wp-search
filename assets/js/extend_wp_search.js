class ExtendWpSearch {
    constructor(wrapper, options = {}) {
        // Default settings
        this.wrapper = wrapper;
        this.settings = Object.assign({
            searchTrigger: extend_wp_search_vars.trigger || '', // Set from // Assuming a general trigger selector
            formSelector: '#ewps-search-form',
            submitButton: '#submit',
            moreResultsButton: '#more-results-button', // Add more-results button
            resultsContainer: '#search-results',
            filtersContainer: '#search_form_filter',
            bodyClass: 'full-screen-open',
            fullScreenClass: 'full-screen-open-left',
            fullScreenPosition: wrapper.getAttribute('full-screen') || false,
            dataTrigger: 'data-trigger',
            inputLength: 3,
            pagination: extend_wp_search_vars.pagination || 'numbers',
            liveSearchInterval: 249,
            normalFormSubmit: false // Flag to handle normal form submission
        }, options);

        // Use the wrapper (each instance will have its own context)

        this.typingTimer = null;
        this.initialize(); // Initialize everything
    }

    // Method to initialize all event listeners and expose global methods
    initialize() {
        this.liveSearch();
        this.autoTrigger();
        this.initPagination();

        // Cache form, button, and more-results button to avoid multiple lookups
        const submitButton = this.wrapper.querySelector(this.settings.submitButton);
        const searchTriggerElement = this.settings.searchTrigger != '' ? document.querySelector(this.settings.searchTrigger) : false;

        const undoButton = this.wrapper.querySelector('#undo-checkboxes');
        const applyButton = this.wrapper.querySelector('#apply-checkboxes');

        const searchBarTriggerElement = this.wrapper.querySelector('#search-trigger');
        const filterTriggerElement = this.wrapper.querySelector('#filter-trigger');
        const closeTriggerElement = this.wrapper.querySelector('#close-trigger');
        // Event listener for search trigger
        if (searchBarTriggerElement) {
            searchBarTriggerElement.addEventListener('click', () => {
                if (this.wrapper.querySelector('.search-bar').classList.contains('show-results')) {
                    this.search(); // Call the search method
                }
                else {
                    submitButton.click();
                }
            });
        }

        // Event listener for filter trigger
        if (filterTriggerElement) {
            filterTriggerElement.addEventListener('click', () => {
                this.changeSearchContainer(filterTriggerElement); // Change search container
            });
        }

        // Event listener for close trigger
        if (closeTriggerElement) {
            closeTriggerElement.addEventListener('click', () => {
                this.extend_wp_search_close_search(); // Close the search container
            });
        }


        // Event listener for undo button (remove filters)
        if (undoButton) {
            undoButton.addEventListener('click', () => {
                this.disableCheckboxes(); // Calls the method to disable the checkboxes
            });
        }

        // Event listener for apply button (apply filters)
        if (applyButton) {
            applyButton.addEventListener('click', () => {
                this.newSearch(); // Calls the method to start a new search
            });
        }


        // Event listener for the submit button
        if (submitButton) {
            submitButton.addEventListener('click', (e) => {
                if (this.wrapper.querySelector('.search-bar').classList.contains('show-results')) {
                // Prevent the default form submission only if normalFormSubmit is false
                if (!this.settings.normalFormSubmit) {
                    e.preventDefault();
                    this.search(); // Calling search method
                }
                }
            });
        }

        // Check if a custom search trigger is provided in the wrapper
        if (searchTriggerElement && this.settings.fullScreenPosition) {
            searchTriggerElement.addEventListener('click', () => {
                if (document.body.classList.contains('ewps-search-page-results')) {
                    window.scrollTo({ top: this.wrapper.querySelector(this.settings.formSelector).offsetTop - 200, behavior: 'smooth' });
                    return;
                }
                this.toggleFullScreen();
            });
        }


    }

    // Method to trigger the search (can be called globally now)
    search() {
        const filterTrigger = this.wrapper.querySelector('#filter-trigger');
        if (this.wrapper.querySelector(this.settings.filtersContainer) != null && this.wrapper.querySelector(this.settings.filtersContainer).classList.contains('active')) {
            this.changeSearchContainer(filterTrigger);
        }
        this.searchQuery();
    }

    // Method to handle live search functionality
    liveSearch() {
        const input = this.wrapper.querySelector('input[name="searchtext"]');


        if (!input || !this.wrapper.querySelector('.search-bar').classList.contains('show-results')) return;

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
        const container = this.wrapper;
        // Get the elements for results and filter
        const searchResults = container.querySelector('#search_form_resutls');
        const searchFormFilter = container.querySelector('#search_form_filter');

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
        const checkboxes = this.wrapper.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        this.search(); // Calling search method
    }

    // Method to trigger search automatically
    autoTrigger() {
        const searchForm = this.wrapper.querySelector('#search_form');
        if (searchForm && parseInt(searchForm.getAttribute(this.settings.dataTrigger)) === 1) {
            this.search(); // Calling search method
        }
    }

    // Method to execute the search query
    searchQuery() {
        const container = this.wrapper;
        const input = this.wrapper.querySelector('input[name="searchtext"]');
        /*check input length*/
        if (input.value.length < this.settings.inputLength) {
            return;
        }

        const form = container.querySelector(this.settings.formSelector);
        if (!form) return;

        /*add pagination type to data*/
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
                const html_data = JSON.parse(data);
                const resultsContainer = this.wrapper.querySelector(this.settings.resultsContainer);

                if (this.settings.pagination === 'button') {
                    const resultsWrapper = resultsContainer.querySelector('.results-wrapper');
                    if (resultsWrapper && this.wrapper.querySelector('input[name="paged"]').value > 1) {
                        resultsWrapper.innerHTML += html_data.results;
                        if (html_data.button !== '') {
                            this.wrapper.querySelector('.ewps-pagination').innerHTML = html_data.button;
                        }
                    } else {
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
        const formData = new FormData(form);
        let hasNonPagedChanges = false;
        const currentQuery = new URLSearchParams(formData);
        const currentQueryString = currentQuery.toString();
        const previousQuery = this.settings.previousQueryString
            ? new URLSearchParams(this.settings.previousQueryString)
            : new URLSearchParams();

        for (const [key, value] of currentQuery.entries()) {
            if (key !== 'paged') {
                if (value !== previousQuery.get(key)) {
                    hasNonPagedChanges = true;
                    break;
                }
            }
        }

        if (hasNonPagedChanges) {
            form.querySelector('input[name="paged"]').value = 1;
        }

        if (this.settings.previousQueryString) {
            const isSameQuery = currentQueryString === this.settings.previousQueryString;
            if (isSameQuery) return true;
        }

        this.settings.previousQueryString = currentQueryString;
        return false;
    }

    // Method to check if the full screen button is visible
    checkFullScreenButton() {
        const moreResultsButton = this.wrapper.querySelector(this.settings.moreResultsButton);
        if (moreResultsButton) {
            moreResultsButton.addEventListener('click', () => {
                const submitButton = this.wrapper.querySelector(this.settings.submitButton);
                if (submitButton) {
                    this.settings.normalFormSubmit = true;
                    submitButton.click();
                }
            });
        }
    }

    // Method to toggle loading state
    setLoading(isLoading) {
        let loadingDiv = this.wrapper.querySelector(this.settings.resultsContainer);
        if (this.settings.pagination === 'button' && this.wrapper.querySelector('input[name="paged"]').value > 1) {
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
    static create(wrapper, options) {
        return new ExtendWpSearch(wrapper, options);
    }

    // Method to update settings dynamically
    update(newSettings) {
        this.settings = Object.assign(this.settings, newSettings);
    }

    // Method to destroy the instance
    destroy() {
        clearTimeout(this.typingTimer);
        const submitButton = this.wrapper.querySelector(this.settings.submitButton);
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
        const loadMoreButton = this.wrapper.querySelector('.ewps-load-more');
        if (loadMoreButton) {
            loadMoreButton.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(loadMoreButton.getAttribute('data-page')) + 1;
                loadMoreButton.setAttribute('data-page', page);
                this.wrapper.querySelector('input[name="paged"]').value = page;
                this.search();
            });
        }
    }

    // Attach click events to pagination links
    attachPaginationEvents() {
        const paginationLinks = this.wrapper.querySelectorAll('.ewps-pagination a.page-numbers');
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.innerText) || parseInt(link.getAttribute('href').split('page/')[1]);
                this.wrapper.querySelector('input[name="paged"]').value = page;
                this.search();
            });
        });
    }

    // Handle infinite scroll (auto-load next page)
    handleInfiniteScroll() {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            const loadMoreButton = this.wrapper.querySelector('.ewps-load-more');
            if (loadMoreButton) {
                const page = parseInt(loadMoreButton.getAttribute('data-page')) + 1;
                this.wrapper.querySelector('input[name="paged"]').value = page;
                this.search();
            }
        }
    }
}

// Instantiate for each search form instance
document.addEventListener('DOMContentLoaded', () => {
    const searchForms = document.querySelectorAll('.ewps-search-interface');
    searchForms.forEach(formElement => {
        ExtendWpSearch.create(formElement, {
            liveSearchInterval: 300
        });
    });
});