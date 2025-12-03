document.addEventListener("DOMContentLoaded", () => {

    const userData = JSON.parse(localStorage.getItem("loggedInUser"));
    if (!userData) {
        alert("Login dulu sebelum checkout");
        window.location.href = "../../User/login/login.html";
        return;
    }

    document.getElementById("checkout_username").textContent = userData.Username;
    document.getElementById("checkout_email").textContent = userData.Email;
    document.getElementById("checkout_phone").textContent = userData.No_Telp;

    const order = JSON.parse(sessionStorage.getItem("pendingOrder"));
    if (!order) {
        alert("Tidak ada pesanan!");
        window.location.href = "../../menu/menu.html";
        return;
    }

    const serviceFee = 5000;
    const totalAsli = order.total ?? order.rawTotal ?? 0;
    const grandTotal = totalAsli + serviceFee;

    sessionStorage.setItem("ServiceFee", serviceFee);
    sessionStorage.setItem("GrandTotal", grandTotal);

    document.getElementById("menu_name").textContent = order.menuName;
    document.getElementById("menu_qty").textContent = order.quantity;
    document.getElementById("menu_price").textContent = order.formattedPrice;
    document.getElementById("menu_total").textContent = order.formattedTotal;
    document.getElementById("Service_Fee").textContent = formatRupiah(serviceFee);
    document.getElementById("Grand_Total").textContent = formatRupiah(grandTotal);

    // ====================== SUBMIT ======================
    document.getElementById("btnSubmitOrder").addEventListener("click", async () => {

        const orderType = window.currentOrderType || "takeaway";

        const formData = new FormData();
        formData.append("ID_Menu", order.menuId);
        formData.append("Jumlah_Pesanan", order.quantity);
        formData.append("Order_Type", orderType);
        formData.append("Nomor_Meja", document.getElementById("tableNumber")?.value || "");
        formData.append("Alamat_Pengantaran", document.getElementById("deliveryAddress")?.value || "");
        formData.append("Notes", document.getElementById("notes")?.value || "");
        formData.append("Total_Bayar", grandTotal);

        try {
            const response = await fetch("pesan.php", {
                method: "POST",
                body: formData
            });

            const text = await response.text();
            console.log("Raw response:", text);

            const result = JSON.parse(text);

            if (result.success) {

                const orderData = {
                menuName: order.menuName,
                quantity: order.quantity,
                grandTotal: grandTotal,
        // tambahan baru
                orderType: orderType,
                tableNumber: document.getElementById("tableNumber")?.value || "",
                deliveryAddress: document.getElementById("deliveryAddress")?.value || "",
                notes: document.getElementById("notes")?.value || ""
        };
                // Simpan untuk halaman payment
                sessionStorage.setItem("orderSummary", JSON.stringify(orderData));
                window.location.href = "../Proses/payment.html"
            } else {
                alert("Error: " + result.message);
            }
        } catch (err) {
            console.error(err);
            alert("Gagal memproses pesanan!");
        }
    });

});

function formatRupiah(nominal) {
    return "Rp " + nominal.toLocaleString("id-ID");
}

function selectOrderType(el, type) {
    const options = document.querySelectorAll(".order-type-option");
    options.forEach(opt => opt.classList.remove("selected"));

    el.classList.add("selected");

    document.getElementById("tableNumberGroup").style.display = type === "dinein" ? "block" : "none";
    document.getElementById("deliveryGroup").style.display = type === "delivery" ? "block" : "none";

    window.currentOrderType = type;
}
