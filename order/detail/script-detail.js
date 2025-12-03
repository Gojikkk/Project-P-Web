// ====================================
// GLOBAL VARIABLES
// ====================================
let currentProduct = null;
let productId = null;
let currentQuantity = 1;
let allMenuItems = [];

// ====================================
// UTILITY FUNCTIONS
// ====================================

// Format currency to Rupiah
function formatPrice(price) {
    return `Rp ${price.toLocaleString('id-ID')}`;
}

// Get product ID from URL
function getProductIdFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return parseInt(urlParams.get('id'));
}

// ====================================
// API FUNCTIONS
// ====================================

// Load all menu items from backend
async function loadAllMenuItems() {
    try {
        const response = await fetch('../../menu/menu.php');
        const data = await response.json();
        
        console.log('Menu data loaded:', data);
        allMenuItems = data;
        return data;
    } catch (error) {
        console.error('Error loading menu:', error);
        alert('Gagal memuat data menu');
        return [];
    }
}

// Calculate total from backend
async function calculateTotalFromBackend() {
    if (!currentProduct || !productId) {
        updateTotalLocal();
        return;
    }

    try {
    const response = await fetch('../../menu/menu.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        action: 'order',
        menuId: productId,
        quantity: currentQuantity
    })
});

        const data = await response.json();
        console.log('Calculate response:', data);

        if (data.success) {
            document.getElementById('totalPrice').textContent = data.formattedTotal;
        } else {
            console.error('Calculate error:', data.message);
            updateTotalLocal();
        }
    } catch (error) {
        console.error('Error calculating total:', error);
        updateTotalLocal();
    }
}

// Send order to backend
async function sendOrderToBackend() {
    if (!productId) {
        alert('Product ID tidak valid');
        return null;
    }

    try {
        const response = await fetch('../../menu/menu.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',   // ⬅⬅⬅ FIX PALING PENTING
        body: JSON.stringify({
        action: 'order',
        menuId: productId,
        quantity: currentQuantity
    })
});

        const data = await response.json();
        console.log('Order response:', data);

        if (data.success) {
            return data;
        } else {
            // FIXED: Check if user needs to login
            if (data.needLogin) {
                alert(data.message);
                window.location.href = '../../login/login.html';
                return null;
            }
            
            alert('Error: ' + data.message);
            return null;
        }
    } catch (error) {
        console.error('Error creating order:', error);
        alert('Terjadi kesalahan saat membuat order. Silakan coba lagi.');
        return null;
    }
}

// ====================================
// UI UPDATE FUNCTIONS
// ====================================

// Update total locally
function updateTotalLocal() {
    if (!currentProduct) return;
    const total = currentProduct.price * currentQuantity;
    document.getElementById('totalPrice').textContent = formatPrice(total);
}

// Display product details
function displayProductDetails() {
    if (!currentProduct) return;

    console.log('Displaying product:', currentProduct);

    // Update title
    document.getElementById('productTitle').textContent = currentProduct.name;
    
    // Update description
    document.getElementById('productDescription').textContent = currentProduct.description;
    
    // Update price
    document.getElementById('productPrice').textContent = formatPrice(currentProduct.price);
    
    // Update category
    document.getElementById('categoryName').textContent = currentProduct.category;
    
    // Update image
    const imageContainer = document.getElementById('productImage');
    if (currentProduct.image) {
        const imagePath = '../../menu/' + currentProduct.image;
        imageContainer.innerHTML = `<img src="${imagePath}" alt="${currentProduct.name}">`;
    } else {
        imageContainer.innerHTML = '<i class="fas fa-mug-hot"></i>';
    }

    // Show popular badge if applicable
    if (currentProduct.popular) {
        document.getElementById('popularBadge').style.display = 'flex';
    }

    // Set initial quantity
    document.getElementById('quantity').value = currentQuantity;
    
    // Update total
    updateTotalLocal();
}

// Load recommendations
function loadRecommendations() {
    if (!currentProduct || allMenuItems.length === 0) return;

    // Filter recommendations: same category, different product
    let recommendations = allMenuItems.filter(item => 
        item.id !== productId && item.category === currentProduct.category
    ).slice(0, 3);

    // If not enough, add different category items
    if (recommendations.length < 3) {
        const additionalItems = allMenuItems.filter(item => 
            item.id !== productId && !recommendations.includes(item)
        ).slice(0, 3 - recommendations.length);
        
        recommendations = [...recommendations, ...additionalItems];
    }

    const grid = document.getElementById('recommendationsGrid');
    grid.innerHTML = recommendations.map(item => {
        const imagePath = '../../menu/' + item.image;
        return `
            <div class="recommendation-card" onclick="goToProduct(${item.id})">
                <div class="rec-image">
                    ${item.image ? `<img src="${imagePath}" alt="${item.name}">` : '<i class="fas fa-mug-hot"></i>'}
                </div>
                <div class="rec-info">
                    <div class="rec-name">${item.name}</div>
                    <div class="rec-description">${item.description}</div>
                    <div class="rec-price">${formatPrice(item.price)}</div>
                </div>
            </div>
        `;
    }).join('');
}

// ====================================
// MAIN FUNCTIONS
// ====================================

// Load product details
async function loadProductDetails() {
    productId = getProductIdFromURL();
    
    console.log('Loading product ID:', productId);
    
    if (!productId || isNaN(productId)) {
        alert('Product ID tidak ditemukan');
        window.location.href = '../../menu/menu.html';
        return;
    }

    // Load all menu items
    await loadAllMenuItems();
    
    // Find current product
    currentProduct = allMenuItems.find(item => item.id === productId);
    
    console.log('Current product found:', currentProduct);
    
    if (!currentProduct) {
        alert('Product tidak ditemukan');
        window.location.href = '../../menu/menu.html';
        return;
    }

    // Display product details
    displayProductDetails();
    
    // Load recommendations
    loadRecommendations();
}

// Increase quantity
function increaseQty() {
    currentQuantity++;
    document.getElementById('quantity').value = currentQuantity;
    calculateTotalFromBackend();
}

// Decrease quantity
function decreaseQty() {
    if (currentQuantity > 1) {
        currentQuantity--;
        document.getElementById('quantity').value = currentQuantity;
        calculateTotalFromBackend();
    }
}

// Order now
async function orderNow() {
    const orderBtn = document.getElementById('orderBtn');
    const originalHTML = orderBtn.innerHTML;
    orderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    orderBtn.disabled = true;

    const orderData = await sendOrderToBackend();

    if (orderData) {
        // FIXED: Correct path to checkout
        // From: order/detail/ 
        // To: order/Proses/checkout.html
        
        // Store order data in sessionStorage for checkout page
        sessionStorage.setItem('currentOrder', JSON.stringify({
            orderId: orderData.orderId,
            menuId: orderData.menuId,
            menuName: orderData.menuName,
            quantity: orderData.quantity,
            price: orderData.price,
            total: orderData.total,
            formattedTotal: orderData.formattedTotal
        }));
        
        // Redirect to checkout page
        window.location.href = `../Proses/checkout.html?orderId=${orderData.orderId}`;
    } else {
        orderBtn.innerHTML = originalHTML;
        orderBtn.disabled = false;
    }
}

// Navigate to product
function goToProduct(id) {
    window.location.href = `../../order/detail/menu-detail.html?id=${id}`;
}

// ====================================
// EVENT LISTENERS
// ====================================

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    loadProductDetails();
    
    // Add event listeners
    document.getElementById('increaseBtn').addEventListener('click', increaseQty);
    document.getElementById('decreaseBtn').addEventListener('click', decreaseQty);
    document.getElementById('orderBtn').addEventListener('click', orderNow);
});

// Handle keyboard shortcuts
document.addEventListener('keydown', function(event) {
    if (event.key === '+' || event.key === '=') {
        event.preventDefault();
        increaseQty();
    }
    
    if (event.key === '-') {
        event.preventDefault();
        decreaseQty();
    }
    
    if (event.key === 'Enter') {
        event.preventDefault();
        orderNow();
    }
});

// Export for onclick
window.goToProduct = goToProduct;