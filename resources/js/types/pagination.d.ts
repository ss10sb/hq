// Laravel Length Aware Paginator TypeScript Types

/**
 * Link object for pagination navigation
 */
export interface PaginationLink {
    url: string | null;
    label: string;
    page: number;
    active: boolean;
}

/**
 * Laravel Length Aware Paginator structure with generic items
 * @template T - The type of items in the data array
 */
export interface LengthAwarePaginator<T = any> {
    /**
     * The current page number
     */
    current_page: number;

    /**
     * Array of paginated items
     */
    data: T[];

    /**
     * Index of the first item on the current page
     */
    first_page_url: string;

    /**
     * The starting index of items on the current page
     */
    from: number | null;

    /**
     * The last page number
     */
    last_page: number;

    /**
     * URL for the last page
     */
    last_page_url: string;

    /**
     * Array of pagination links for navigation
     */
    links: PaginationLink[];

    /**
     * URL for the next page (null if on last page)
     */
    next_page_url: string | null;

    /**
     * The current path without query parameters
     */
    path: string;

    /**
     * Number of items per page
     */
    per_page: number;

    /**
     * URL for the previous page (null if on first page)
     */
    prev_page_url: string | null;

    /**
     * The ending index of items on the current page
     */
    to: number | null;

    /**
     * Total number of items across all pages
     */
    total: number;
}

/**
 * Simplified paginator interface for basic use cases
 * @template T - The type of items in the data array
 */
export interface SimplePaginator<T = any> {
    current_page: number;
    data: T[];
    from: number | null;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
}

/**
 * Meta information about pagination (useful for API responses)
 */
export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
}

/**
 * Paginated API response structure (Laravel API Resources)
 * @template T - The type of items in the data array
 */
export interface PaginatedResponse<T = any> {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: PaginationMeta & {
        links: PaginationLink[];
    };
}
