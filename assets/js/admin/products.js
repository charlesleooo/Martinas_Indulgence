function deleteProduct(id) {
    if(confirm('Are you sure you want to delete this product?')) {
        window.location.href = 'products.php?delete=' + id;
    }
}

function editProduct(id) {
    // Fetch product details using AJAX
    fetch('get_product.php?id=' + id)
        .then(response => response.json())
        .then(product => {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_price').value = product.price;
            
            // Set the category dropdown
            const categorySelect = document.getElementById('edit_category');
            for(let i = 0; i < categorySelect.options.length; i++) {
                if(categorySelect.options[i].value === product.category) {
                    categorySelect.selectedIndex = i;
                    break;
                }
            }
            
            document.getElementById('edit_current_image').value = product.image;
            document.getElementById('edit_image_preview').src = product.image;
            document.getElementById('editProductModal').classList.remove('hidden');
        });
}

function previewNewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('edit_image_preview').src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}