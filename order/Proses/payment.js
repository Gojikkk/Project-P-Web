document.addEventListener("DOMContentLoaded", () => {

    const userData = JSON.parse(localStorage.getItem("loggedInUser"));
    if (!userData) {
        alert("Login dulu sebelum checkout!");
        window.location.href = "../../User/login/login.html";
        return;
    }

    const order = JSON.parse(sessionStorage.getItem("pendingOrder"));
    if (!order) {
        alert("Tidak ada data order, silakan checkout terlebih dahulu!");
        window.location.href = "../../order/checkout/checkout.html";
        return;
    }

    // ===== Hitung subtotal & grandTotal =====
    const serviceFee = order.serviceFee ?? 5000;
    const subtotal = order.total ?? 0;
    const grandTotal = order.grandTotal ?? (subtotal + serviceFee);

    // Ambil orderType
    const orderType = order.orderType?.toLowerCase() ?? "takeaway";

    // Tampilkan info customer
    const customerInfo = document.getElementById("customerInfo");
    customerInfo.innerHTML = `
        <p><strong>Name:</strong> ${userData.Username}</p>
        <p><strong>Phone:</strong> ${userData.No_Telp || userData.Phone}</p>
        <p><strong>Order Type:</strong> ${orderType}</p>
        ${order.tableNumber ? `<p><strong>Table:</strong> ${order.tableNumber}</p>` : ''}
        ${order.deliveryAddress ? `<p><strong>Address:</strong> ${order.deliveryAddress}</p>` : ''}
        ${order.notes ? `<p><strong>Notes:</strong> ${order.notes}</p>` : ''}
    `;

    // Tampilkan order items
    const orderItems = document.getElementById("orderItems");
    if (order.items && order.items.length > 0) {
        orderItems.innerHTML = order.items.map(item => `
            <div class="order-item">
                <div class="item-info">
                    <span class="item-name">${item.name}</span>
                    <span class="item-qty">Qty: ${item.quantity}</span>
                </div>
                <div class="item-price">Rp ${item.total.toLocaleString('id-ID')}</div>
            </div>
        `).join('');
    } else {
        const price = order.total ?? grandTotal;
        orderItems.innerHTML = `
            <div class="order-item">
                <div class="item-info">
                    <span class="item-name">${order.menuName}</span>
                    <span class="item-qty">Qty: ${order.quantity}</span>
                </div>
                <div class="item-price">Rp ${price.toLocaleString('id-ID')}</div>
            </div>
        `;
    }

    // Update totals
    document.getElementById("total").textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
    document.getElementById("serviceFee").textContent = `Rp ${serviceFee.toLocaleString('id-ID')}`;
    document.getElementById("grandTotal").textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
    document.getElementById("payAmount").textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;

    let selectedPaymentMethod = null;

    // ===== Pilih payment method =====
    window.selectPaymentMethod = (method) => {
        selectedPaymentMethod = method;

        document.querySelectorAll(".payment-method").forEach(opt => opt.classList.remove("selected"));
        const el = Array.from(document.querySelectorAll(".payment-method"))
                        .find(e => e.onclick.toString().includes(method));
        if (el) el.classList.add("selected");

        const paymentDetails = document.getElementById("paymentDetails");
        const accountNumbers = {
            gopay: '0812-3456-7890',
            ovo: '0812-3456-7890',
            dana: '0812-3456-7890',
            bca: '8271-0283-4912',
            mandiri: '8932-1746-2837',
            creditcard: 'Secure Gateway'
        };
        const instructions = {
            gopay: 'Transfer via Gopay app. Payment will be verified automatically.',
            ovo: 'Transfer via OVO app. Payment will be verified automatically.',
            dana: 'Transfer via DANA app. Payment will be verified automatically.',
            bca: 'Transfer ke BCA Virtual Account. Payment akan diverifikasi otomatis.',
            mandiri: 'Transfer ke Mandiri Virtual Account. Payment akan diverifikasi otomatis.',
            creditcard: 'You will be redirected to secure gateway for payment.'
        };

        paymentDetails.innerHTML = method === 'creditcard'
            ? `<p>${instructions[method]}</p>`
            : `<p><strong>Account Number:</strong> ${accountNumbers[method]}</p><p>${instructions[method]}</p>`;

        document.getElementById("paymentError").style.display = "none";
        document.getElementById("payButton").disabled = false;
    };

    // ===== Submit pembayaran =====
    window.processPayment = async () => {
        if (!selectedPaymentMethod) {
            document.getElementById("paymentError").style.display = "block";
            return;
        }

        // ✅ PERBAIKAN: Definisikan variable sebelum digunakan
        const menu_id = order.menuId;
        const quantity = order.quantity ?? (order.items?.[0]?.quantity ?? 1);
        const price = order.price ?? (order.items?.[0]?.price ?? (subtotal / quantity));
        const total = grandTotal;

        // Debug log
        console.log("=== PAYMENT DATA ===");
        console.log("menu_id:", menu_id);
        console.log("quantity:", quantity);
        console.log("price:", price);
        console.log("total:", total);
        console.log("payment_method:", selectedPaymentMethod);
        console.log("===================");

        if (total <= 0) {
            alert("Total harga tidak valid: " + total);
            return;
        }

        const formData = new FormData();
        formData.append("menu_id", menu_id);
        formData.append("quantity", quantity);
        formData.append("tota", price);
        formData.append("total", total);
        formData.append("service_fee", serviceFee);
        formData.append("payment_methode", selectedPaymentMethod);
        formData.append("name", userData.Username);
        formData.append("phone", userData.No_Telp || userData.Phone);
        formData.append("order_type", orderType);
        if (order.tableNumber) formData.append("table_number", order.tableNumber);
        if (order.deliveryAddress) formData.append("address", order.deliveryAddress);
        if (order.notes) formData.append("notes", order.notes);

        // Debug: cek data sebelum submit
        console.log("=== FormData ===");
        for (let pair of formData.entries()) {
            console.log(pair[0] + ": " + pair[1]);
        }
        console.log("================");

        try {
            const response = await fetch("payment.php", {
                method: "POST",
                body: formData
            });

            const text = await response.text();
            console.log("Raw response:", text);

            const result = JSON.parse(text);
            console.log("Parsed result:", result);

            if (result.success) {
                // === SIMPAN DATA ORDER KE SESSION ===============
    const orderData = {
        pesanan_id: result.pesanan_id,

        customer_name: userData.Username,
        order_type: orderType,
        payment_method: selectedPaymentMethod,
        order_date: new Date().toLocaleString("id-ID"),
        total: total,

        // === ITEM PESANAN ===
        items: order.items && order.items.length > 0
            ? order.items.map(i => ({
                nama: i.name,
                qty: i.quantity,
                subtotal: i.total
            }))
            : [{
                nama: order.menuName,
                qty: quantity,
                subtotal: total
            }]
    };

    sessionStorage.setItem("orderData", JSON.stringify(orderData));
                sessionStorage.removeItem("orderSummary");
                window.location.href = "../confirm/order-confirmed.html";
            } else {
                alert("Error: " + result.message);
            }
        } catch (err) {
            console.error("Error:", err);
            alert("Gagal memproses pembayaran! " + err.message);
        }
    };

});