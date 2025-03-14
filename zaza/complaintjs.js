/**
 * CampusCare JavaScript Functionality
 * Provides interactive features and animations for the campus maintenance system
 */

document.addEventListener('DOMContentLoaded', function() {
    // Format dates to be more readable
    formatDates();
    
    // Add form validation
    setupFormValidation();
    
    // Initialize interactive elements
    initializeTooltips();
    
    // Add animation to status indicators
    animateStatusIndicators();
    
    // Setup filtering functionality
    setupFiltering();
});

/**
 * Format dates to be more user-friendly
 */
function formatDates() {
    const dates = document.querySelectorAll('.date');
    
    dates.forEach(date => {
        const timestamp = date.getAttribute('data-submitted');
        if (timestamp) {
            const formattedDate = new Date(timestamp).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            date.textContent = formattedDate;
        }
    });
}

/**
 * Set up form validation with visual feedback
 */
function setupFormValidation() {
    const form = document.getElementById('complaint-form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('invalid');
                    
                    // Add shake animation to invalid fields
                    field.classList.add('shake');
                    setTimeout(() => {
                        field.classList.remove('shake');
                    }, 500);
                    
                    // Create error message if not exists
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMessage = document.createElement('div');
                        errorMessage.classList.add('error-message');
                        errorMessage.textContent = `${field.placeholder || field.name} is required`;
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                } else {
                    field.classList.remove('invalid');
                    
                    // Remove error message if exists
                    if (field.nextElementSibling && field.nextElementSibling.classList.contains('error-message')) {
                        field.nextElementSibling.remove();
                    }
                }
            });
            
            if (!isValid) {
                event.preventDefault();
            } else {
                // Add loading state to button
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                submitButton.disabled = true;
            }
        });
        
        // Real-time validation
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('input', function() {
                if (field.hasAttribute('required') && field.value.trim()) {
                    field.classList.remove('invalid');
                    
                    // Remove error message if exists
                    if (field.nextElementSibling && field.nextElementSibling.classList.contains('error-message')) {
                        field.nextElementSibling.remove();
                    }
                }
            });
        });
    }
}

/**
 * Initialize tooltips for additional information
 */
function initializeTooltips() {
    const statusElements = document.querySelectorAll('.status-pending, .status-in_progress, .status-resolved');
    
    statusElements.forEach(element => {
        const statusType = element.classList[0].replace('status-', '');
        let tooltipText;
        
        if (statusType === 'pending') {
            tooltipText = 'Your complaint is waiting to be reviewed';
        } else if (statusType === 'in_progress') {
            tooltipText = 'Staff is currently working on your complaint';
        } else if (statusType === 'resolved') {
            tooltipText = 'Your complaint has been resolved';
        }
        
        element.setAttribute('title', tooltipText);
        element.setAttribute('data-tooltip', tooltipText);
        
        // Simple tooltip functionality
        element.addEventListener('mouseenter', function(event) {
            const tooltip = document.createElement('div');
            tooltip.classList.add('tooltip');
            tooltip.textContent = element.getAttribute('data-tooltip');
            
            document.body.appendChild(tooltip);
            
            // Position the tooltip
            const rect = element.getBoundingClientRect();
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10 + window.scrollY}px`;
            tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
            
            // Store reference to the tooltip
            element.tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (element.tooltip) {
                element.tooltip.remove();
                element.tooltip = null;
            }
        });
    });
}

/**
 * Add animations to status indicators
 */
function animateStatusIndicators() {
    const inProgressElements = document.querySelectorAll('.status-in_progress');
    
    inProgressElements.forEach(element => {
        // Add pulsing animation
        element.classList.add('pulsing');
    });
    
    const resolvedElements = document.querySelectorAll('.status-resolved');
    
    resolvedElements.forEach(element => {
        // Add checkmark animation
        element.classList.add('check-mark-animation');
    });
}

/**
 * Setup filtering for complaints
 */
function setupFiltering() {
    // Create filter controls if there are complaints
    const complaintsContainer = document.querySelector('.complaints-container');
    
    if (complaintsContainer && complaintsContainer.children.length > 0) {
        // Create filter section
        const filterSection = document.createElement('div');
        filterSection.classList.add('filter-section');
        filterSection.innerHTML = `
            <div class="filter-title">
                <i class="fas fa-filter"></i>
                <h3>Filter Complaints</h3>
            </div>
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="status-filter">Status</label>
                    <select id="status-filter" class="form-control">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="category-filter">Category</label>
                    <select id="category-filter" class="form-control">
                        <option value="all">All Categories</option>
                        <option value="facilities">Facilities</option>
                        <option value="technology">Technology</option>
                        <option value="security">Security</option>
                        <option value="sanitation">Sanitation</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="search-filter">Search</label>
                    <input type="text" id="search-filter" class="form-control" placeholder="Search in complaints...">
                </div>
            </div>
        `;
        
        // Insert filter section before complaints container
        complaintsContainer.parentNode.insertBefore(filterSection, complaintsContainer);
        
        // Add event listeners for filtering
        const statusFilter = document.getElementById('status-filter');
        const categoryFilter = document.getElementById('category-filter');
        const searchFilter = document.getElementById('search-filter');
        
        const filterComplaints = () => {
            const statusValue = statusFilter.value;
            const categoryValue = categoryFilter.value;
            const searchValue = searchFilter.value.toLowerCase();
            
            const complaints = complaintsContainer.querySelectorAll('.complaint-card');
            
            complaints.forEach(complaint => {
                let showComplaint = true;
                
                // Filter by status
                if (statusValue !== 'all') {
                    const statusElement = complaint.querySelector(`[class^="status-"]`);
                    if (statusElement && !statusElement.classList.contains(`status-${statusValue}`)) {
                        showComplaint = false;
                    }
                }
                
                // Filter by category
                if (showComplaint && categoryValue !== 'all') {
                    const categoryElement = complaint.querySelector('.category');
                    if (categoryElement && !categoryElement.classList.contains(`category-${categoryValue}`)) {
                        showComplaint = false;
                    }
                }
                
                // Filter by search text
                if (showComplaint && searchValue !== '') {
                    const complaintText = complaint.textContent.toLowerCase();
                    if (!complaintText.includes(searchValue)) {
                        showComplaint = false;
                    }
                }
                
                // Show or hide the complaint
                complaint.style.display = showComplaint ? 'block' : 'none';
                
                // Add animation when showing
                if (showComplaint) {
                    complaint.style.animation = 'none';
                    void complaint.offsetWidth; // Trigger reflow
                    complaint.style.animation = 'fadeIn 0.5s ease-out';
                }
            });
            
            // Show message if no results
            let noResultsMsg = document.querySelector('.no-results-message');
            
            // Check if any complaints are visible
            const visibleComplaints = [...complaints].filter(c => c.style.display !== 'none');
            
            if (visibleComplaints.length === 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.classList.add('no-results-message', 'alert');
                    noResultsMsg.innerHTML = '<i class="fas fa-info-circle"></i> No complaints match your filters.';
                    complaintsContainer.after(noResultsMsg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        };
        
        // Add event listeners
        statusFilter.addEventListener('change', filterComplaints);
        categoryFilter.addEventListener('change', filterComplaints);
        searchFilter.addEventListener('input', filterComplaints);
    }
}

// Add additional CSS classes dynamically
document.head.insertAdjacentHTML('beforeend', `
<style>
    /* Additional Animations */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    @keyframes grow {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    /* Tooltip styling */
    .tooltip {
        position: absolute;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        z-index: 1000;
        pointer-events: none;
        animation: grow 0.2s ease-out;
    }
    
    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: rgba(0, 0, 0, 0.8) transparent transparent transparent;
    }
    
    /* Error message styling */
    .error-message {
        color: #e74a3b;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        animation: fadeIn 0.3s ease-out;
    }
    
    .error-message::before {
        content: '\\f06a';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        margin-right: 0.25rem;
    }
    
    /* Invalid field styling */
    .invalid {
        border-color: #e74a3b !important;
    }
    
    .shake {
        animation: shake 0.5s;
    }
    
    /* Pulsing animation for in-progress status */
    .pulsing {
        animation: pulse 2s infinite;
    }
    
    /* Filter section styling */
    .filter-section {
        background-color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .filter-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .filter-title i {
        color: var(--primary);
    }
    
    .filter-title h3 {
        margin: 0;
    }
    
    .filter-controls {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .filter-group {
        margin-bottom: 0;
    }
    
    .no-results-message {
        background-color: rgba(108, 117, 125, 0.2);
        color: var(--gray);
        text-align: center;
    }
    
    /* Loading spinner */
    .fa-spinner {
        animation: rotate 1s linear infinite;
    }
</style>
`);