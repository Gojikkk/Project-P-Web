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
  return `Rp ${price.toLocaleString("id-ID")}`;
}

// Get product ID from URL
function getProductIdFromURL() {
  const urlParams = new URLSearchParams(window.location.search);
  return parseInt(urlParams.get("id"));
}

// ====================================
// API FUNCTIONS
// ====================================

// Load all menu items from backend
async function loadAllMenuItems() {
  try {
    // PERBAIKAN: Path ke menu.php dari order/detail/ ke menu/
    const response = await fetch("../../menu/menu.php");
    const data = await response.json();

    console.log("Menu data loaded:", data);
    allMenuItems = data;
    return data;
  } catch (error) {
    console.error("Error loading menu:", error);
    alert("Gagal memuat data menu");
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
    const response = await fetch("detail.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "calculate",
        menuId: productId,
        quantity: currentQuantity,
      }),
    });

    const data = await response.json();
    console.log("Calculate response:", data);

    if (data.success) {
      document.getElementById("totalPrice").textContent = data.formattedTotal;
    } else {
      console.error("Calculate error:", data.message);
      updateTotalLocal();
    }
  } catch (error) {
    console.error("Error calculating total:", error);
    updateTotalLocal();
  }
}

// ====================================
// UI UPDATE FUNCTIONS
// ====================================

// Update total locally
function updateTotalLocal() {
  if (!currentProduct) return;
  const total = currentProduct.price * currentQuantity;
  document.getElementById("totalPrice").textContent = formatPrice(total);
}

// Display product details
function displayProductDetails() {
  if (!currentProduct) return;

  console.log("Displaying product:", currentProduct);

  document.getElementById("productTitle").textContent = currentProduct.name;
  document.getElementById("productDescription").textContent =
    currentProduct.description;
  document.getElementById("productPrice").textContent = formatPrice(
    currentProduct.price
  );
  document.getElementById("categoryName").textContent = currentProduct.category;

  const imageContainer = document.getElementById("productImage");
  if (currentProduct.image) {
    // Gambar path dari menu.php adalah 'Gambar/filename.jpg'
    // Dari order/detail/ folder, kita perlu '../../menu/Gambar/filename.jpg'
    const imagePath = "../../menu/" + currentProduct.image;
    imageContainer.innerHTML = `<img src="${imagePath}" alt="${currentProduct.name}">`;
  } else {
    imageContainer.innerHTML = '<i class="fas fa-mug-hot"></i>';
  }

  if (currentProduct.popular) {
    document.getElementById("popularBadge").style.display = "flex";
  }

  document.getElementById("quantity").value = currentQuantity;
  updateTotalLocal();
}

// Load recommendations
function loadRecommendations() {
  if (!currentProduct || allMenuItems.length === 0) return;

  let recommendations = allMenuItems
    .filter(
      (item) =>
        item.id !== productId && item.category === currentProduct.category
    )
    .slice(0, 3);

  if (recommendations.length < 3) {
    const additionalItems = allMenuItems
      .filter(
        (item) => item.id !== productId && !recommendations.includes(item)
      )
      .slice(0, 3 - recommendations.length);

    recommendations = [...recommendations, ...additionalItems];
  }

  const grid = document.getElementById("recommendationsGrid");
  grid.innerHTML = recommendations
    .map((item) => {
      const imagePath = "../../menu/" + item.image;
      return `
            <div class="recommendation-card" onclick="goToProduct(${item.id})">
                <div class="rec-image">
                    ${
                      item.image
                        ? `<img src="${imagePath}" alt="${item.name}">`
                        : '<i class="fas fa-mug-hot"></i>'
                    }
                </div>
                <div class="rec-info">
                    <div class="rec-name">${item.name}</div>
                    <div class="rec-description">${item.description}</div>
                    <div class="rec-price">${formatPrice(item.price)}</div>
                </div>
            </div>
        `;
    })
    .join("");
}

// ====================================
// MAIN FUNCTIONS
// ====================================

// Load product details
async function loadProductDetails() {
  productId = getProductIdFromURL();

  console.log("Loading product ID:", productId);

  if (!productId || isNaN(productId)) {
    alert("Product ID tidak ditemukan");
    window.location.href = "../../menu/menu.html";
    return;
  }

  await loadAllMenuItems();
  currentProduct = allMenuItems.find((item) => item.id === productId);

  console.log("Current product found:", currentProduct);

  if (!currentProduct) {
    alert("Product tidak ditemukan");
    window.location.href = "../../menu/menu.html";
    return;
  }

  displayProductDetails();
  loadRecommendations();
}

// Increase quantity
function increaseQty() {
  currentQuantity++;
  document.getElementById("quantity").value = currentQuantity;
  calculateTotalFromBackend();
}

// Decrease quantity
function decreaseQty() {
  if (currentQuantity > 1) {
    currentQuantity--;
    document.getElementById("quantity").value = currentQuantity;
    calculateTotalFromBackend();
  }
}

// Order now - SIMPAN DATA SEMENTARA, BELUM KE DATABASE
async function orderNow() {
  if (!currentProduct || !productId) {
    alert("Data produk tidak valid");
    return;
  }

  const orderBtn = document.getElementById("orderBtn");
  const originalHTML = orderBtn.innerHTML;
  orderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
  orderBtn.disabled = true;

  try {
    // Harga asli
    const total = currentProduct.price * currentQuantity;

    // SERVICE FEE
    const serviceFee = 5000;

    // GRAND TOTAL (harga + service fee)
    const grandTotal = total + serviceFee;

    // Data order lengkap
    const orderData = {
      menuId: productId,
      menuName: currentProduct.name,
      menuImage: currentProduct.image,
      menuCategory: currentProduct.category,
      price: currentProduct.price,
      quantity: currentQuantity,

      // total asli
      total: total,

      // fee + total akhir
      serviceFee: serviceFee,
      grandTotal: grandTotal,

      formattedPrice: formatPrice(currentProduct.price),
      formattedTotal: formatPrice(total),

      formattedServiceFee: formatPrice(serviceFee),
      formattedGrandTotal: formatPrice(grandTotal),

      timestamp: new Date().toISOString(),
    };

    // SIMPAN KE SESSION STORAGE
    sessionStorage.setItem("pendingOrder", JSON.stringify(orderData));
    sessionStorage.setItem("serviceFee", serviceFee);
    sessionStorage.setItem("grandTotal", grandTotal);

    console.log("Order + service fee saved:", orderData);

    // Pindah ke checkout
    setTimeout(() => {
      window.location.href = `../checkout/checkout.html`;
    }, 500);
  } catch (error) {
    console.error("Error preparing order:", error);
    alert("Terjadi kesalahan. Silakan coba lagi.");
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

document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded, initializing...");
  loadProductDetails();

  document.getElementById("increaseBtn").addEventListener("click", increaseQty);
  document.getElementById("decreaseBtn").addEventListener("click", decreaseQty);
  document.getElementById("orderBtn").addEventListener("click", orderNow);
});

document.addEventListener("keydown", function (event) {
  if (event.key === "+" || event.key === "=") {
    event.preventDefault();
    increaseQty();
  }

  if (event.key === "-") {
    event.preventDefault();
    decreaseQty();
  }

  if (event.key === "Enter") {
    event.preventDefault();
    orderNow();
  }
});

window.goToProduct = goToProduct;
