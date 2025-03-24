// Track Order Form Submission
const trackOrderForm = document.getElementById('trackOrderForm');
const orderStatus = document.getElementById('orderStatus');

trackOrderForm.addEventListener('submit', (e) => {
    e.preventDefault(); // Prevent form submission

    // Get form values
    const orderNumber = document.getElementById('orderNumber').value.trim();
    const email = document.getElementById('email').value.trim();

    // Simple validation
    if (!orderNumber || !email) {
        alert('Please fill out all fields.');
        return;
    }

    // Simulate fetching order status (replace with actual API call)
    const fakeOrderStatus = {
        orderNumber: '123456',
        status: 'Shipped',
        estimatedDelivery: 'December 25, 2023',
        trackingLink: '#'
    };

    // Display order status
    if (orderNumber === fakeOrderStatus.orderNumber && email === 'test@example.com') {
        orderStatus.innerHTML = `
            <h3>Order Status</h3>
            <p><strong>Order Number:</strong> ${fakeOrderStatus.orderNumber}</p>
            <p><strong>Status:</strong> ${fakeOrderStatus.status}</p>
            <p><strong>Estimated Delivery:</strong> ${fakeOrderStatus.estimatedDelivery}</p>
            <p><strong>Tracking Link:</strong> <a href="${fakeOrderStatus.trackingLink}" target="_blank">Track Package</a></p>
        `;
    } else {
        orderStatus.innerHTML = `
            <h3>Order Status</h3>
            <p>No order found with the provided details. Please check your order number and email address.</p>
        `;
    }
});