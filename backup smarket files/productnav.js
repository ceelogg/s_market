// =============================
// Modal Open/Close Logic
// =============================
function openAddModal() {
    const modal = document.getElementById("editModal");
    const modalTitle = document.getElementById("modalTitle");
    const form = document.getElementById("editProductForm");
    const submitBtn = document.getElementById("modalSubmitBtn");
    
    if (!modal || !modalTitle || !form || !submitBtn) {
        console.error("Modal elements not found!");
        return;
    }
    
    form.reset();
    document.getElementById("edit_id").value = "";
    
    modalTitle.textContent = "Add New Product";
    submitBtn.name = "add_product";
    submitBtn.textContent = "Add Product";
    modal.style.display = "block";
    
    // Set up month auto-fill for Add Modal
    setupMonthAutoFill();
}

function editProduct(product) {
    const modal = document.getElementById("editModal");
    const modalTitle = document.getElementById("modalTitle");
    const submitBtn = document.getElementById("modalSubmitBtn");
    
    if (!modal || !modalTitle || !submitBtn) {
        console.error("Modal elements not found!");
        return;
    }
    
    const setValue = (id, value) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    };
    
    setValue("edit_id", product.id);
    setValue("edit_branch", product.branch);
    setValue("edit_product_type", product.product_type);
    setValue("edit_product_name", product.product_name);
    setValue("edit_quantity_sold", product.quantity_sold);
    setValue("edit_unit_price", product.unit_price);
    setValue("edit_date_of_sale", product.date_of_sale);
    setValue("edit_month_of_sale", product.month_of_sale);
    
    modalTitle.textContent = "Edit Product";
    submitBtn.name = "update_product";
    submitBtn.textContent = "Save Changes";
    modal.style.display = "block";
    
    // Set up month auto-fill for Edit Modal
    setupMonthAutoFill();
}

function closeModal() {
    const modal = document.getElementById("editModal");
    if (modal) modal.style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("editModal");
    if (modal && event.target == modal) {
        closeModal();
    }
};

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeModal();
});

// =============================
// Month Auto-Fill Logic
// =============================
function setupMonthAutoFill() {
    const dateInput = document.getElementById("edit_date_of_sale");
    const monthInput = document.getElementById("edit_month_of_sale");

    if (!dateInput || !monthInput) {
        console.warn("Date or Month input not found in modal");
        return;
    }

    // Remove any existing event listeners to prevent duplicates
    const newDateInput = dateInput.cloneNode(true);
    dateInput.parentNode.replaceChild(newDateInput, dateInput);

    // Auto-update month field on date change
    newDateInput.addEventListener("change", function() {
        const selectedDate = new Date(this.value);
        if (!isNaN(selectedDate)) {
            const monthName = selectedDate.toLocaleString("default", { month: "long" });
            monthInput.value = monthName;
        } else {
            monthInput.value = "";
        }
    });

    // Auto-fill if editing an existing record with a valid date
    if (newDateInput.value) {
        const selectedDate = new Date(newDateInput.value);
        if (!isNaN(selectedDate)) {
            const monthName = selectedDate.toLocaleString("default", { month: "long" });
            monthInput.value = monthName;
        }
    }
}

// =============================
// Form Validation
// =============================
document.addEventListener('DOMContentLoaded', function() {
    const editProductForm = document.getElementById('editProductForm');
    
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            const branchSelect = document.getElementById('edit_branch');
            const productTypeSelect = document.getElementById('edit_product_type');
            const quantitySoldInput = document.getElementById('edit_quantity_sold');
            const unitPriceInput = document.getElementById('edit_unit_price');
            const productNameInput = document.getElementById('edit_product_name');
            const dateOfSaleInput = document.getElementById('edit_date_of_sale');
            const monthOfSaleInput = document.getElementById('edit_month_of_sale');

            if (branchSelect && !branchSelect.value.trim()) {
                e.preventDefault();
                alert('Please select a branch');
                branchSelect.focus();
                return false;
            }

            if (productTypeSelect && !productTypeSelect.value.trim()) {
                e.preventDefault();
                alert('Please select a product type');
                productTypeSelect.focus();
                return false;
            }

            if (quantitySoldInput && parseInt(quantitySoldInput.value) < 0) {
                e.preventDefault();
                alert('Quantity sold cannot be negative');
                quantitySoldInput.focus();
                return false;
            }

            if (unitPriceInput && parseFloat(unitPriceInput.value) < 0) {
                e.preventDefault();
                alert('Unit price cannot be negative');
                unitPriceInput.focus();
                return false;
            }

            if (productNameInput && !productNameInput.value.trim()) {
                e.preventDefault();
                alert('Please enter a product name');
                productNameInput.focus();
                return false;
            }

            if (dateOfSaleInput && !dateOfSaleInput.value) {
                e.preventDefault();
                alert('Please select a date of sale');
                dateOfSaleInput.focus();
                return false;
            }

            if (monthOfSaleInput && !monthOfSaleInput.value) {
                e.preventDefault();
                alert('Month is required. Please select a valid date.');
                dateOfSaleInput.focus();
                return false;
            }

            return true;
        });
    }

    // Add field highlight behavior
    const style = document.createElement('style');
    style.textContent = `
        .form-group select:valid {
            border-color: #4CAF50;
        }
        .form-group select:invalid:not(:focus) {
            border-color: #ff9800;
        }
        .form-group select:focus:invalid {
            border-color: #f44336;
        }
    `;
    document.head.appendChild(style);

    // Add smooth scrolling for pagination
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Smooth scroll to top when changing pages
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
});

// Make sure functions are globally accessible
window.openAddModal = openAddModal;
window.editProduct = editProduct;
window.closeModal = closeModal;
window.setupMonthAutoFill = setupMonthAutoFill;

console.log('productnav.js loaded successfully');