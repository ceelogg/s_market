// Simple version that doesn't rely on DOMContentLoaded
function openAddModal() {
    const modal = document.getElementById("editModal");
    const modalTitle = document.getElementById("modalTitle");
    
    if (!modal || !modalTitle) {
        console.error("Modal elements not found!");
        return;
    }
    
    // Clear the form for new product entry
    const form = document.getElementById("editProductForm");
    if (form) form.reset();
    
    const editId = document.getElementById("edit_id");
    if (editId) editId.value = ""; // Clear ID for new product
    
    // Change modal title to Add Product
    modalTitle.textContent = "Add New Product";
    
    // Show the modal
    modal.style.display = "block";
}

function editProduct(product) {
    const modal = document.getElementById("editModal");
    const modalTitle = document.getElementById("modalTitle");
    
    if (!modal || !modalTitle) {
        console.error("Modal elements not found!");
        return;
    }
    
    // Populate form with existing product data
    const setValue = (id, value) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    };
    
    setValue("edit_id", product.id);
    
    // Set branch dropdown - handle both select and input elements
    const branchElement = document.getElementById("edit_branch");
    if (branchElement) {
        if (branchElement.tagName === 'SELECT') {
            // It's a dropdown select
            branchElement.value = product.branch;
        } else {
            // It's an input field (fallback)
            branchElement.value = product.branch;
        }
    }
    
    // Set product type dropdown - handle both select and input elements
    const productTypeElement = document.getElementById("edit_product_type");
    if (productTypeElement) {
        if (productTypeElement.tagName === 'SELECT') {
            // It's a dropdown select
            productTypeElement.value = product.product_type;
        } else {
            // It's an input field (fallback)
            productTypeElement.value = product.product_type;
        }
    }
    
    setValue("edit_product_name", product.product_name);
    setValue("edit_product_quantity", product.product_quantity);
    setValue("edit_quantity_sold", product.quantity_sold);
    setValue("edit_unit_price", product.unit_price);
    setValue("edit_date_of_sales", product.date_of_sales);
    setValue("edit_month_of_sales", product.month_of_sales);
    
    // Change modal title to Edit Product
    modalTitle.textContent = "Edit Product";
    
    // Show the modal
    modal.style.display = "block";
}

function closeModal() {
    const modal = document.getElementById("editModal");
    if (modal) {
        modal.style.display = "none";
    }
}

// Close the modal if user clicks outside of it
window.onclick = function(event) {
    const modal = document.getElementById("editModal");
    if (modal && event.target == modal) {
        closeModal();
    }
}

// Close modal when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const editProductForm = document.getElementById('editProductForm');
    
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            // Validate dropdown selections
            const branchSelect = document.getElementById('edit_branch');
            if (branchSelect && branchSelect.tagName === 'SELECT') {
                if (!branchSelect.value) {
                    e.preventDefault();
                    alert('Please select a branch');
                    branchSelect.focus();
                    return false;
                }
            }
            
            const productTypeSelect = document.getElementById('edit_product_type');
            if (productTypeSelect && productTypeSelect.tagName === 'SELECT') {
                if (!productTypeSelect.value) {
                    e.preventDefault();
                    alert('Please select a product type');
                    productTypeSelect.focus();
                    return false;
                }
            }
            
            // Validate quantity sold
            const quantitySoldInput = document.getElementById('edit_quantity_sold');
            if (quantitySoldInput) {
                const quantitySold = parseInt(quantitySoldInput.value);
                if (quantitySold < 0) {
                    e.preventDefault();
                    alert('Quantity sold cannot be negative');
                    quantitySoldInput.focus();
                    return false;
                }
            }
            
            // Validate unit price
            const unitPriceInput = document.getElementById('edit_unit_price');
            if (unitPriceInput) {
                const unitPrice = parseFloat(unitPriceInput.value);
                if (unitPrice < 0) {
                    e.preventDefault();
                    alert('Unit price cannot be negative');
                    unitPriceInput.focus();
                    return false;
                }
            }
            
            // Validate stock quantity
            const stockInput = document.getElementById('edit_product_quantity');
            if (stockInput) {
                const stock = parseInt(stockInput.value);
                if (stock < 0) {
                    e.preventDefault();
                    alert('Stock quantity cannot be negative');
                    stockInput.focus();
                    return false;
                }
            }
            
            // Validate product name
            const productNameInput = document.getElementById('edit_product_name');
            if (productNameInput && !productNameInput.value.trim()) {
                e.preventDefault();
                alert('Please enter a product name');
                productNameInput.focus();
                return false;
            }
            
            return true;
        });
    }
    
    // Enhanced dropdown styling (adds visual feedback)
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
});

// Enhanced error handling and debugging
console.log('productnav.js loaded successfully');

// Make sure functions are available globally
window.openAddModal = openAddModal;
window.editProduct = editProduct;
window.closeModal = closeModal;

// Additional utility function to handle both input and select elements
function setFormValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        if (element.tagName === 'SELECT') {
            // For dropdown selects
            element.value = value;
        } else {
            // For input fields
            element.value = value;
        }
    }
}