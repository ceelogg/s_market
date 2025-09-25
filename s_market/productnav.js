const modal = document.getElementById("editModal");

// Function to open the modal and populate form with product data
function editProduct(product) {
    document.getElementById("edit_id").value = product.id;
    document.getElementById("edit_category").value = product.category;
    document.getElementById("edit_name").value = product.name;
    document.getElementById("edit_quantity").value = product.quantity;
    document.getElementById("edit_original_price").value = product.original_price;
    document.getElementById("edit_retail_price").value = product.retail_price;
    document.getElementById("edit_sales").value = product.sales;
    
    modal.style.display = "block";
}

function closeModal() {
    modal.style.display = "none";
}

// Close the modal if user clicks outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

