// FitFusion Backend Simulation
document.addEventListener('DOMContentLoaded', function() {
    // Simulate product data that would normally come from a backend
    const products = [
        {
            id: 1,
            name: "PulseTrack Pro X7",
            category: "Wearable Tech",
            price: 249.99,
            oldPrice: 299.99,
            image: "/api/placeholder/300/250",
            badge: "NEW",
            stock: 15,
            description: "Advanced fitness tracking with heart rate monitoring, sleep analysis, and workout suggestions."
        },
        {
            id: 2,
            name: "FlexForce Adjustable Dumbbells",
            category: "Strength Training",
            price: 179.99,
            oldPrice: 229.99,
            image: "/api/placeholder/300/250",
            badge: "BEST SELLER",
            stock: 8,
            description: "Adjustable weight from 5-50 lbs with easy dial system for quick changes during workouts."
        },
        {
            id: 3,
            name: "UltraFlex Compression Tights",
            category: "Performance Wear",
            price: 59.99,
            oldPrice: 79.99,
            image: "/api/placeholder/300/250",
            badge: "",
            stock: 23,
            description: "Premium compression technology for muscle support and improved circulation during workouts."
        },
        {
            id: 4,
            name: "PowerCore Resistance Bands",
            category: "Accessories",
            price: 29.99,
            oldPrice: 39.99,
            image: "/api/placeholder/300/250",
            badge: "SALE",
            stock: 50,
            description: "Set of 5 resistance bands with different tension levels for versatile home workouts."
        }
    ];

    // Shopping cart functionality
    let cart = JSON.parse(localStorage.getItem('fitfusion_cart')) || [];
    
    // Update cart count in header
    function updateCartCount() {
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = cart.length;
        }
    }
    
    // Initialize cart count
    updateCartCount();
    
    // Add to cart functionality
    document.querySelectorAll('.btn-primary').forEach((button, index) => {
        if (button.textContent === 'Add to Cart') {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Create loading effect
                const originalText = this.textContent;
                this.textContent = 'Adding...';
                this.disabled = true;
                
                // Simulate API call delay
                setTimeout(() => {
                    // Get product info from our simulated database
                    const product = products[index >= products.length ? index % products.length : index];
                    
                    // Add to cart
                    cart.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        quantity: 1
                    });
                    
                    // Save cart to localStorage
                    localStorage.setItem('fitfusion_cart', JSON.stringify(cart));
                    
                    // Update UI
                    updateCartCount();
                    this.textContent = 'Added ✓';
                    
                    // Show notification
                    showNotification(`${product.name} added to cart!`);
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                    }, 2000);
                }, 800); // Simulating network delay
            });
        }
    });
    
    // Create a notification system
    function showNotification(message) {
        // Create notification element if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 1000;
            `;
            document.body.appendChild(notificationContainer);
        }
        
        // Create notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;
        
        // Add notification to container
        const container = document.getElementById('notification-container');
        container.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 4 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                container.removeChild(notification);
            }, 300);
        }, 4000);
    }
    
    // Quick view functionality for product details
    document.querySelectorAll('.btn-outline').forEach((button, index) => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get product info
            const product = products[index >= products.length ? index % products.length : index];
            
            // Create modal if it doesn't exist
            if (!document.getElementById('product-modal')) {
                const modal = document.createElement('div');
                modal.id = 'product-modal';
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.8);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;
                
                const modalContent = document.createElement('div');
                modalContent.style.cssText = `
                    background-color: #111;
                    border-radius: 20px;
                    padding: 30px;
                    max-width: 800px;
                    width: 90%;
                    position: relative;
                    transform: translateY(-20px);
                    transition: transform 0.3s ease;
                `;
                
                const closeButton = document.createElement('button');
                closeButton.textContent = '×';
                closeButton.style.cssText = `
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    background: none;
                    border: none;
                    font-size: 30px;
                    color: white;
                    cursor: pointer;
                `;
                closeButton.addEventListener('click', () => {
                    modal.style.opacity = '0';
                    modalContent.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        document.body.removeChild(modal);
                    }, 300);
                });
                
                modalContent.appendChild(closeButton);
                modal.appendChild(modalContent);
                document.body.appendChild(modal);
                
                // Animate in
                setTimeout(() => {
                    modal.style.opacity = '1';
                    modalContent.style.transform = 'translateY(0)';
                }, 10);
            }
            
            modalContent.innerHTML = `
    <!-- Previous content remains the same until the input fields -->
    
    <!-- Shipping Information -->
    <div style="margin-bottom: 30px;">
        <h3 style="font-size: 1.2rem; color: white; margin-bottom: 15px;">Shipping Information</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">First Name *</label>
                <input type="text" id="first-name" required pattern="[A-Za-z ]+" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem; outline: none;">
                <small style="color: red; display: none;" id="first-name-error">Please enter a valid name (letters only)</small>
            </div>
            <div>
                <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">Last Name *</label>
                <input type="text" id="last-name" required pattern="[A-Za-z ]+" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem; outline: none;">
                <small style="color: red; display: none;" id="last-name-error">Please enter a valid name (letters only)</small>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">Email *</label>
            <input type="email" id="email" required style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem; outline: none;">
            <small style="color: red; display: none;" id="email-error">Please enter a valid email</small>
        </div>
        <!-- Rest of the form fields with similar validation -->
    </div>
    
    <!-- Payment Information with similar validation -->
`; 
            // Update modal content
            const modalContent = document.querySelector('#product-modal > div');
            modalContent.innerHTML = `
                <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 30px; color: white; cursor: pointer;">×</button>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <img src="${product.image}" alt="${product.name}" style="width: 100%; border-radius: 10px;">
                    </div>
                    <div>
                        <div style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">${product.category}</div>
                        <h2 style="font-size: 2rem; margin: 10px 0; color: white;">${product.name}</h2>
                        <div style="margin: 20px 0;">
                            <span style="font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #00ffcc, #ff00cc); -webkit-background-clip: text; background-clip: text; color: transparent;">$${product.price}</span>
                            <span style="text-decoration: line-through; color: rgba(255, 255, 255, 0.4); margin-left: 10px;">$${product.oldPrice}</span>
                        </div>
                        <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.7; margin-bottom: 20px;">${product.description}</p>
                        <div style="color: rgba(255, 255, 255, 0.6); margin-bottom: 20px;">
                            <span style="color: ${product.stock > 10 ? '#00ffcc' : product.stock > 5 ? 'orange' : 'red'};">
                                ${product.stock > 10 ? 'In Stock' : product.stock > 5 ? 'Low Stock' : 'Very Low Stock'} 
                            </span>
                            (${product.stock} units)
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button class="modal-add-to-cart" style="flex: 1; padding: 15px; border: none; border-radius: 50px; background: linear-gradient(135deg, #00ffcc, #ff00cc); color: black; font-weight: 600; cursor: pointer;">Add to Cart</button>
                            <button style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255, 255, 255, 0.1); border: none; color: white; cursor: pointer;">❤️</button>
                        </div>
                    </div>
                </div>
            `;
            
            // Close modal when clicking X
            document.querySelector('#product-modal > div > button').addEventListener('click', () => {
                const modal = document.getElementById('product-modal');
                modal.style.opacity = '0';
                modalContent.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 300);
            });
            
            // Add to cart from modal
            document.querySelector('.modal-add-to-cart').addEventListener('click', function() {
                // Create loading effect
                const originalText = this.textContent;
                this.textContent = 'Adding...';
                this.disabled = true;
                
                // Simulate API call delay
                setTimeout(() => {
                    // Add to cart
                    cart.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        quantity: 1
                    });
                    
                    // Save cart to localStorage
                    localStorage.setItem('fitfusion_cart', JSON.stringify(cart));
                    
                    // Update UI
                    updateCartCount();
                    this.textContent = 'Added ✓';
                    
                    // Show notification
                    showNotification(`${product.name} added to cart!`);
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                    }, 2000);
                }, 800); // Simulating network delay
            });
        });
    });
    
    // Cart icon functionality
    document.querySelector('.cart-icon').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Show cart modal
        if (!document.getElementById('cart-modal')) {
            const modal = document.createElement('div');
            modal.id = 'cart-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                right: 0;
                width: 400px;
                max-width: 100%;
                height: 100%;
                background-color: #111;
                z-index: 1000;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                box-shadow: -5px 0 30px rgba(0, 0, 0, 0.5);
            `;
            
            // Create cart content
            let cartContent = `
                <div style="padding: 20px; height: 100%; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="font-size: 1.5rem; color: white;">Your Cart (${cart.length})</h2>
                        <button id="close-cart" style="background: none; border: none; font-size: 24px; color: white; cursor: pointer;">×</button>
                    </div>
                    <div style="flex-grow: 1; overflow-y: auto;">
            `;
            
            if (cart.length === 0) {
                cartContent += `
                    <div style="text-align: center; padding: 50px 0;">
                        <div style="font-size: 50px; margin-bottom: 20px;">🛒</div>
                        <p style="color: rgba(255, 255, 255, 0.6);">Your cart is empty</p>
                        <button id="continue-shopping" style="margin-top: 20px; padding: 10px 20px; background: linear-gradient(135deg, #00ffcc, #ff00cc); border: none; border-radius: 50px; color: black; font-weight: 600; cursor: pointer;">Continue Shopping</button>
                    </div>
                `;
            } else {
                let total = 0;
                
                cart.forEach(item => {
                    total += item.price * item.quantity;
                    
                    cartContent += `
                        <div style="display: flex; margin-bottom: 20px; padding: 15px; background: rgba(255, 255, 255, 0.05); border-radius: 10px;">
                            <div style="width: 80px; height: 80px; background: #222; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <div style="font-size: 24px;">🏋️</div>
                            </div>
                            <div style="flex-grow: 1; padding: 0 15px;">
                                <h3 style="font-size: 1rem; color: white; margin-bottom: 5px;">${item.name}</h3>
                                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">Quantity: ${item.quantity}</p>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-size: 1.1rem; font-weight: 600; background: linear-gradient(135deg, #00ffcc, #ff00cc); -webkit-background-clip: text; background-clip: text; color: transparent;">$${(item.price * item.quantity).toFixed(2)}</p>
                                <button class="remove-item" data-id="${item.id}" style="background: none; border: none; color: rgba(255, 255, 255, 0.6); font-size: 0.8rem; cursor: pointer; margin-top: 5px;">Remove</button>
                            </div>
                        </div>
                    `;
                });
                
                cartContent += `
                    </div>
                    <div style="padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: rgba(255, 255, 255, 0.6);">Subtotal:</span>
                            <span style="font-weight: 600; color: white;">$${total.toFixed(2)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: rgba(255, 255, 255, 0.6);">Shipping:</span>
                            <span style="font-weight: 600; color: white;">$${(total > 100 ? 0 : 9.99).toFixed(2)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                            <span style="color: white; font-weight: 600;">Total:</span>
                            <span style="font-size: 1.2rem; font-weight: 700; background: linear-gradient(135deg, #00ffcc, #ff00cc); -webkit-background-clip: text; background-clip: text; color: transparent;">$${(total + (total > 100 ? 0 : 9.99)).toFixed(2)}</span>
                        </div>
                        <button id="checkout-button" style="width: 100%; padding: 15px; background: linear-gradient(135deg, #00ffcc, #ff00cc); border: none; border-radius: 50px; color: black; font-weight: 600; cursor: pointer; margin-bottom: 10px;">Proceed to Checkout</button>
                        <button id="continue-shopping" style="width: 100%; padding: 15px; background: transparent; border: 1px solid rgba(0, 255, 204, 0.5); border-radius: 50px; color: white; font-weight: 600; cursor: pointer;">Continue Shopping</button>
                    </div>
                `;
            }
            
            cartContent += `</div>`;
            modal.innerHTML = cartContent;
            document.body.appendChild(modal);
            
            // Animate in
            setTimeout(() => {
                modal.style.transform = 'translateX(0)';
            }, 10);
            
            // Close cart when clicking X
            document.getElementById('close-cart').addEventListener('click', () => {
                modal.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 300);
            });
            
            // Continue shopping button
            document.getElementById('continue-shopping').addEventListener('click', () => {
                modal.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 300);
            });
            
            // Remove item functionality
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = parseInt(this.getAttribute('data-id'));
                    
                    // Create loading effect
                    const originalText = this.textContent;
                    this.textContent = 'Removing...';
                    this.disabled = true;
                    
                    // Simulate API call delay
                    setTimeout(() => {
                        // Remove from cart
                        cart = cart.filter(item => item.id !== itemId);
                        
                        // Save cart to localStorage
                        localStorage.setItem('fitfusion_cart', JSON.stringify(cart));
                        
                        // Update UI
                        updateCartCount();
                        
                        // Show notification
                        showNotification('Item removed from cart!');
                        
                        // Reload cart modal
                        document.body.removeChild(modal);
                        document.querySelector('.cart-icon').click();
                    }, 600);
                });
            });
            
            // Checkout button functionality
            if (document.getElementById('checkout-button')) {
                document.getElementById('checkout-button').addEventListener('click', function() {
                    // Create loading effect
                    const originalText = this.textContent;
                    this.textContent = 'Processing...';
                    this.disabled = true;
                    
                    // Simulate API call delay
                    setTimeout(() => {
                        // Close cart modal
                        modal.style.transform = 'translateX(100%)';
                        setTimeout(() => {
                            document.body.removeChild(modal);
                        }, 300);
                        
                        // Create checkout modal
                        createCheckoutModal();
                    }, 1000);
                });
            }
        }
    });
    
    // Create checkout modal
    function createCheckoutModal() {
        const modal = document.createElement('div');
        modal.id = 'checkout-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        const modalContent = document.createElement('div');
        modalContent.style.cssText = `
            background-color: #111;
            border-radius: 20px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        `;
        
        modalContent.innerHTML = `
            <h2 style="font-size: 1.8rem; color: white; margin-bottom: 20px; text-align: center;">Checkout</h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 1.2rem; color: white; margin-bottom: 15px;">Shipping Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">First Name</label>
                        <input type="text" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">Last Name</label>
                        <input type="text" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">Email</label>
                    <input type="email" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                </div>
                <div style="margin-top: 15px;">
                    <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">Address</label>
                    <input type="text" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">City</label>
                        <input type="text" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">State</label>
                        <input type="text" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">ZIP</label>
                        <input type="text" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 1.2rem; color: white; margin-bottom: 15px;">Payment Information</h3>
                <div>
                    <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">Card Number</label>
                    <input type="text" placeholder="**** **** **** ****" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">Expiration Date</label>
                        <input type="text" placeholder="MM/YY" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 5px;">CVV</label>
                        <input type="text" placeholder="***" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white; font-size: 1rem;">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; gap: 15px;">
                <button id="back-to-cart" style="flex: 1; padding: 15px; background: transparent; border: 1px solid rgba(0, 255, 204, 0.5); border-radius: 50px; color: white; font-weight: 600; cursor: pointer;">Back to Cart</button>
                <button id="place-order" style="flex: 1; padding: 15px; background: linear-gradient(135deg, #00ffcc, #ff00cc); border: none; border-radius: 50px; color: black; font-weight: 600; cursor: pointer;">Place Order</button>
            </div>
        `;
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Animate in
        setTimeout(() => {
            modal.style.opacity = '1';
            modalContent.style.transform = 'translateY(0)';
        }, 10);
        
        // Back to cart button
        document.getElementById('back-to-cart').addEventListener('click', () => {
            modal.style.opacity = '0';
            modalContent.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                document.body.removeChild(modal);
                document.querySelector('.cart-icon').click();
            }, 300);
        });
        
        // Place order button
        document.getElementById('place-order').addEventListener('click', function() {
            // Get all input fields
            const inputs = modalContent.querySelectorAll('input');
            let isValid = true;
            
            // Validate each field
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = 'red';
                    isValid = false;
                } else {
                    input.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                }
            });
            
            // Specific validations
            const emailInput = modalContent.querySelector('input[type="email"]');
            const cardInput = modalContent.querySelector('input[placeholder="**** **** **** ****"]');
            const expiryInput = modalContent.querySelector('input[placeholder="MM/YY"]');
            const cvvInput = modalContent.querySelector('input[placeholder="***"]');
            
            // Email validation
            if (!emailInput.value.includes('@') || !emailInput.value.includes('.')) {
                emailInput.style.borderColor = 'red';
                isValid = false;
                showNotification('Please enter a valid email address');
            }
            
            // Card number validation (simple 16 digit check)
            if (!/^\d{16}$/.test(cardInput.value.replace(/\s/g, ''))) {
                cardInput.style.borderColor = 'red';
                isValid = false;
                showNotification('Card number must be 16 digits');
            }
            
            // Expiry date validation (MM/YY format)
            if (!/^\d{2}\/\d{2}$/.test(expiryInput.value)) {
                expiryInput.style.borderColor = 'red';
                isValid = false;
                showNotification('Expiry date must be in MM/YY format');
            }
            
            // CVV validation (3-4 digits)
            if (!/^\d{3,4}$/.test(cvvInput.value)) {
                cvvInput.style.borderColor = 'red';
                isValid = false;
                showNotification('CVV must be 3-4 digits');
            }
            
            if (!isValid) {
                return;
            }
            
            // Rest of your existing code for successful validation...
            const originalText = this.textContent;
            this.textContent = 'Processing...';
            this.disabled = true;
            
            // Simulate API call delay
            setTimeout(() => {
                // Close checkout modal
                modal.style.opacity = '0';
                modalContent.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    document.body.removeChild(modal);
                    
                    // Clear cart
                    cart = [];
                    localStorage.setItem('fitfusion_cart', JSON.stringify(cart));
                    updateCartCount();
                    
                    // Show success modal
                    showOrderSuccessModal();
                }, 300);
            }, 2000);
        });
    }
    
    // Show order success modal
    function showOrderSuccessModal() {
        const modal = document.createElement('div');
        modal.id = 'success-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        const modalContent = document.createElement('div');
        modalContent.style.cssText = `
            background-color: #111;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        `;
        
        modalContent.innerHTML = `
            <div style="font-size: 60px; margin-bottom: 20px;">✅</div>
            <h2 style="font-size: 2rem; color: white; margin-bottom: 20px;">Order Placed Successfully!</h2>
            <p style="color: rgba(255, 255, 255, 0.8); margin-bottom: 30px;">Thank you for your purchase. Your order has been received and is being processed. You will receive a confirmation email shortly.</p>
            <div style="background: rgba(255, 255, 255, 0.05); border-radius: 10px; padding: 15px; margin-bottom: 30px;">
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem; margin-bottom: 5px;">Order ID: #${Math.floor(100000 + Math.random() * 900000)}</p>
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">Estimated Delivery: ${getEstimatedDeliveryDate()}</p>
            </div>
            <button id="continue-shopping-success" style="padding: 15px 40px; background: linear-gradient(135deg, #00ffcc, #ff00cc); border: none; border-radius: 50px; color: black; font-weight: 600; cursor: pointer;">Continue Shopping</button>
        `;
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Animate in
        setTimeout(() => {
            modal.style.opacity = '1';
            modalContent.style.transform = 'translateY(0)';
        }, 10);
        
        // Continue shopping button
        document.getElementById('continue-shopping-success').addEventListener('click', () => {
            modal.style.opacity = '0';
            modalContent.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                document.body.removeChild(modal);
            }, 300);
        });
    }
    
    // Helper function to get estimated delivery date (5-7 business days from now)
    function getEstimatedDeliveryDate() {
        const today = new Date();
        const deliveryDate = new Date(today);
        
        // Add 5-7 business days
        const daysToAdd = 5 + Math.floor(Math.random() * 3);
        let businessDaysAdded = 0;
        
        while (businessDaysAdded < daysToAdd) {
            deliveryDate.setDate(deliveryDate.getDate() + 1);
            // Skip weekends (0 = Sunday, 6 = Saturday)
            if (deliveryDate.getDay() !== 0 && deliveryDate.getDay() !== 6) {
                businessDaysAdded++;
            }
        }
        
        // Format the date
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${months[deliveryDate.getMonth()]} ${deliveryDate.getDate()}, ${deliveryDate.getFullYear()}`;
    }
    
    // Add search functionality
    const searchIcon = document.querySelector('.header-icons div:first-child');
    if (searchIcon) {
        searchIcon.style.cursor = 'pointer';
        searchIcon.addEventListener('click', function() {
            // Create search modal
            const modal = document.createElement('div');
            modal.id = 'search-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.95);
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding-top: 100px;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            modal.innerHTML = `
                <div style="width: 600px; max-width: 90%; position: relative;">
                    <button id="close-search" style="position: absolute; top: -50px; right: 0; background: none; border: none; font-size: 24px; color: white; cursor: pointer;">×</button>
                    <div style="position: relative; margin-bottom: 30px;">
                        <input type="text" id="search-input" placeholder="Search for products..." style="width: 100%; padding: 15px 20px; background: rgba(255, 255, 255, 0.1); border: none; border-radius: 50px; color: white; font-size: 1.1rem; outline: none;">
                        <button id="search-button" style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); background: none; border: none; color: white; cursor: pointer;">🔍</button>
                    </div>
                    <div id="search-results" style="display: none; background: #111; border-radius: 15px; padding: 20px; max-height: 400px; overflow-y: auto;"></div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Animate in
            setTimeout(() => {
                modal.style.opacity = '1';
                document.getElementById('search-input').focus();
            }, 10);
            
            // Close search when clicking X
            document.getElementById('close-search').addEventListener('click', () => {
                modal.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 300);
            });
            
            // Search functionality
            const searchInput = document.getElementById('search-input');
            const searchButton = document.getElementById('search-button');
            const searchResults = document.getElementById('search-results');
            
            // Function to perform search
            function performSearch() {
                const query = searchInput.value.toLowerCase().trim();
                
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }
                
                // Show loading
                searchResults.style.display = 'block';
                searchResults.innerHTML = '<p style="text-align: center; color: rgba(255, 255, 255, 0.6);">Searching...</p>';
                
                // Simulate API call delay
                setTimeout(() => {
                    // Filter products based on query
                    const filteredProducts = products.filter(product => 
                        product.name.toLowerCase().includes(query) || 
                        product.category.toLowerCase().includes(query) ||
                        product.description.toLowerCase().includes(query)
                    );
                    
                    // Display results
                    if (filteredProducts.length === 0) {
                        searchResults.innerHTML = '<p style="text-align: center; color: rgba(255, 255, 255, 0.6);">No products found matching your search.</p>';
                    } else {
                        let resultsHTML = '';
                        
                        filteredProducts.forEach(product => {
                            resultsHTML += `
                                <div style="display: flex; gap: 15px; padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                    <div style="width: 60px; height: 60px; background: #222; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <div style="font-size: 20px;">🏋️</div>
                                    </div>
                                    <div style="flex-grow: 1;">
                                        <h3 style="font-size: 1rem; color: white; margin-bottom: 5px;">${product.name}</h3>
                                        <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">${product.category}</p>
                                    </div>
                                    <div style="text-align: right;">
                                        <p style="font-size: 1.1rem; font-weight: 600; background: linear-gradient(135deg, #00ffcc, #ff00cc); -webkit-background-clip: text; background-clip: text; color: transparent;">$${product.price.toFixed(2)}</p>
                                    </div>
                                </div>
                            `;
                        });
                        
                        searchResults.innerHTML = resultsHTML;
                        
                        // Add click event to search results
                        searchResults.querySelectorAll('div[style*="display: flex"]').forEach((result, index) => {
                            result.style.cursor = 'pointer';
                            result.addEventListener('click', () => {
                                // Close search modal
                                modal.style.opacity = '0';
                                setTimeout(() => {
                                    document.body.removeChild(modal);
                                    
                                    // Show product details
                                    const productIndex = products.findIndex(p => p.name === filteredProducts[index].name);
                                    document.querySelectorAll('.btn-outline')[productIndex].click();
                                }, 300);
                            });
                        });
                    }
                }, 800);
            }
            
            // Search on button click
            searchButton.addEventListener('click', performSearch);
            
            // Search on enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
            
            // Live search as user types
            searchInput.addEventListener('input', function() {
                if (this.value.length >= 2) {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(performSearch, 500);
                } else if (this.value.length === 0) {
                    searchResults.style.display = 'none';
                }
            });
        });
    }
    
    // Add newsletter subscription in footer
    const footerAbout = document.querySelector('.footer-about');
    if (footerAbout) {
        // Create newsletter form
        const newsletterForm = document.createElement('div');
        newsletterForm.style.cssText = `
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        `;
        
        newsletterForm.innerHTML = `
            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 15px; position: relative; display: inline-block;">Subscribe to Our Newsletter</h3>
            <p style="color: rgba(255, 255, 255, 0.6); line-height: 1.7; margin-bottom: 15px;">Get the latest updates on new products, special offers, and fitness tips.</p>
            <div style="display: flex; gap: 10px;">
                <input type="email" placeholder="Your email address" style="flex-grow: 1; padding: 12px 15px; background: rgba(255, 255, 255, 0.1); border: none; border-radius: 50px; color: white; font-size: 0.9rem; outline: none;">
                <button id="subscribe-button" style="padding: 12px 20px; background: linear-gradient(135deg, #00ffcc, #ff00cc); border: none; border-radius: 50px; color: black; font-weight: 600; cursor: pointer;">Subscribe</button>
            </div>
        `;
        
        footerAbout.appendChild(newsletterForm);
        
        // Add subscription functionality
        const subscribeButton = document.getElementById('subscribe-button');
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        
        subscribeButton.addEventListener('click', function() {
            const email = emailInput.value.trim();
            
            if (!email || !email.includes('@')) {
                showNotification('Please enter a valid email address!');
                return;
            }
            
            // Create loading effect
            const originalText = this.textContent;
            this.textContent = 'Subscribing...';
            this.disabled = true;
            
            // Simulate API call delay
            setTimeout(() => {
                // Reset form
                emailInput.value = '';
                this.textContent = originalText;
                this.disabled = false;
                
                // Show success notification
                showNotification('Thank you for subscribing to our newsletter!');
            }, 1000);
        });
    }
    
    // Add scroll animation for products
    const productCards = document.querySelectorAll('.product-card');
    
    // Function to check if element is in viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.bottom >= 0
        );
    }
    
    // Function to handle scroll animation
    function handleScrollAnimation() {
        productCards.forEach(card => {
            if (isInViewport(card)) {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }
        });
    }
    
    // Set initial state
    productCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(50px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    // Add scroll event listener
    window.addEventListener('scroll', handleScrollAnimation);
    
    // Initial check in case elements are already in viewport
    handleScrollAnimation();
});