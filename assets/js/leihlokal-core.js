// LEIHLOKAL CORE API INTERFACE
// This module provides a simple interface to the Leihlokal API using PocketBase.
// It handles authentication and provides methods for fetching items and searching.
// The API is initialized lazily, so you don't have to worry about it.
// The API instance is exported as a default export, so you can import it anywhere.
// 
// API Schema:
// Collection 'item':
// - id: string. Item ID in the database. Returned as a string. Not relevant for users or volunteers.
// - iid: number. Item ID on the shelves, SH.IT notation. Returned as a number.
// - name: string. Item title. Returned as a string.
// - description: string. Item description. Returned as a string.
// - status: select. Item status. Returned as a string.
// - deposit: number. Suggested item tare. Returned as a number.
// - images: array. Item images. Returned as an array of strings.
// - cat: multiselect. Item categories. Returned as an array of strings.
// - brand: string. Item brand. Returned as a string.
// - model: string. Item model. Returned as a string.
// - parts: number. Amount of parts of an item. Returned as a number.
// - copies: number. Amount of instances of an item. Returned as a number.
//    SHOULD MENTION: 'copies' is the amount of items available for rent, 
//    'parts' is the amount of parts of one instance of that item.
// - synonyms: string, comma-delimited. Alternative names for items that should also be allowed in search.
// 
// Collection 'reservation':
// Don't worry about it hehe

import PocketBase from 'https://unpkg.com/pocketbase@0.21.1/dist/pocketbase.es.mjs';

class Cart {
    constructor() {
        this.items = this.loadCart();
    }

    loadCart() {
        const savedCart = localStorage.getItem('leihlocalCart');
        return savedCart ? JSON.parse(savedCart) : [];
    }

    saveCart() {
        localStorage.setItem('leihlocalCart', JSON.stringify(this.items));
        this.notifyListeners();
    }

    addItem(item) {
        if (!this.items.some(i => i.id === item.id)) {
            this.items.push({
                id: item.id,
                iid: item.iid,
                name: item.name,
                deposit: item.deposit,
            });
            this.saveCart();
        }
    }

    removeItem(itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
        this.saveCart();
    }

    clearCart() {
        this.items = [];
        this.saveCart();
    }

    // Observer pattern for cart updates
    listeners = new Set();

    subscribe(callback) {
        this.listeners.add(callback);
        return () => this.listeners.delete(callback);
    }

    notifyListeners() {
        this.listeners.forEach(callback => callback(this.items));
    }
}

class LeihlocalAPI {
    constructor() {
        // Use a local PocketBase instance but with our proxy endpoints
        this.pb = new PocketBase('https://stage.leihlokal-ka.de'); // Keep original base URL for image URLs
        this.isInitialized = true; // No need to initialize - we're using the API key via proxy
        this.ITEMS_PER_PAGE = 20;
        this.cart = new Cart();
        this.VALID_STATUSES = ['instock', 'outofstock', 'reserved'];
    }

    // No need for initialize() method anymore!

    // Helper method to get full image URLs (this stays the same)
    getImageUrl(collectionId, recordId, fileName, thumb = '') {
        const baseUrl = this.pb.baseUrl;
        const url = `${baseUrl}/api/files/${collectionId}/${recordId}/${fileName}`;
        return thumb ? `${url}?thumb=${thumb}` : url;
    }

    transformItemImages(item) {
        if (!item.images || !item.images.length) return [];
        
        return item.images.map(imageName => ({
            full: this.getImageUrl('item', item.id, imageName),
            thumb: this.getImageUrl('item', item.id, imageName, '300x300f')
        }));
    }

    async getItems(page = 1, additionalFilter = '') {
        try {
            const options = {
                sort: 'iid'
            };
    
            // Create status filter
            const statusFilter = `status = "${this.VALID_STATUSES.join('" || status = "')}"`;
            
            // Combine with additional filter if provided
            if (additionalFilter) {
                options.filter = `(${statusFilter}) && (${additionalFilter})`;
            } else {
                options.filter = statusFilter;
            }
            
            // Build query parameters
            const params = new URLSearchParams({
                action: 'list-items',
                page: page,
                perPage: this.ITEMS_PER_PAGE,
                sort: options.sort,
                filter: options.filter
            });
            
            // Use the Kirby page with query parameters
            const response = await fetch(`/api?${params}`);
            if (!response.ok) throw new Error(`API error: ${response.status}`);
            
            const data = await response.json();
            const items = typeof data === 'string' ? JSON.parse(data) : data;
            
            const transformedItems = items.items.map(item => ({
                ...item,
                images: this.transformItemImages(item)
            }));
    
            return {
                ...items,
                items: transformedItems
            };
        } catch (error) {
            console.error('Failed to fetch items:', error);
            throw error;
        }
    }
    
    async searchItems(query, page = 1) {
        try {
            // Search filter
            const searchFilter = `name ~ "${query}" || iid ~ "${query}" || synonyms ~ "${query}"`;
            const statusFilter = `status = "${this.VALID_STATUSES.join('" || status = "')}"`;
            const filter = `(${statusFilter}) && (${searchFilter})`;
            
            // Build query parameters
            const params = new URLSearchParams({
                action: 'list-items',
                page: page,
                perPage: this.ITEMS_PER_PAGE,
                filter: filter,
                sort: 'iid',
                fields: 'id,iid,name,description,status,deposit,images,cat,brand,model,parts,copies,synonyms'
            });
            
            const response = await fetch(`/api?${params}`);
            if (!response.ok) throw new Error(`API error: ${response.status}`);
            
            const data = await response.json();
            const items = typeof data === 'string' ? JSON.parse(data) : data;
            
            const transformedItems = items.items.map(item => ({
                ...item,
                images: this.transformItemImages(item)
            }));
    
            return {
                ...items,
                items: transformedItems
            };
        } catch (error) {
            console.error('Failed to search items:', error);
            throw error;
        }
    }
    
    async getItem(id) {
        try {
            const params = new URLSearchParams({
                action: 'get-item',
                id: id
            });
            
            const response = await fetch(`/api?${params}`);
            if (!response.ok) throw new Error(`API error: ${response.status}`);
            
            const data = await response.json();
            const item = typeof data === 'string' ? JSON.parse(data) : data;
            
            const transformedItem = {
                ...item,
                images: this.transformItemImages(item)
            };
            
            return transformedItem;
        } catch (error) {
            console.error('Failed to fetch item:', error);
            throw error;
        }
    }
    
    async createReservation(reservationData) {
        try {
            const response = await fetch('/api?action=create-reservation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(reservationData)
            });
            
            if (!response.ok) throw new Error(`API error: ${response.status}`);
            const data = await response.json();
            return typeof data === 'string' ? JSON.parse(data) : data;
        } catch (error) {
            console.error('Failed to create reservation:', error);
            throw error;
        }
    }
}

const api = new LeihlocalAPI();
export default api;