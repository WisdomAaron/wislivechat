/**
 * WisChat Subscription Manager
 * 
 * Handles MTN Mobile Money subscription payments
 */

(function($) {
    'use strict';

    class WisChatSubscriptionManager {
        constructor() {
            this.apiEndpoint = wischat_admin.api_endpoint;
            this.apiKey = wischat_admin.api_key;
            this.currentPlan = null;
            this.plans = [];
            
            this.init();
        }

        init() {
            this.bindEvents();
            this.loadSubscriptionData();
        }

        bindEvents() {
            $(document).on('click', '.wischat-plan-select', this.handlePlanSelection.bind(this));
            $(document).on('click', '.wischat-subscribe-btn', this.handleSubscription.bind(this));
            $(document).on('click', '.wischat-cancel-subscription', this.handleCancelSubscription.bind(this));
            $(document).on('submit', '#wischat-payment-form', this.handlePaymentSubmission.bind(this));
            $(document).on('click', '.wischat-validate-phone', this.validatePhoneNumber.bind(this));
        }

        async loadSubscriptionData() {
            try {
                // Load subscription plans
                const plansResponse = await this.apiCall('/api/v1/payments/plans', 'GET');
                if (plansResponse.success) {
                    this.plans = plansResponse.plans;
                    this.renderPlans();
                }

                // Load current subscription
                const subscriptionResponse = await this.apiCall('/api/v1/payments/subscription', 'GET');
                if (subscriptionResponse.success) {
                    this.currentPlan = subscriptionResponse.subscription;
                    this.renderCurrentSubscription();
                }

            } catch (error) {
                console.error('Failed to load subscription data:', error);
                this.showError('Failed to load subscription information');
            }
        }

        renderPlans() {
            const container = $('#wischat-subscription-plans');
            if (!container.length) return;

            let html = '<div class="wischat-plans-grid">';

            this.plans.forEach(plan => {
                const isCurrentPlan = this.currentPlan && this.currentPlan.planId === plan.id;
                const price = plan.price > 0 ? `UGX ${plan.price.toLocaleString()}` : 'Free';
                
                html += `
                    <div class="wischat-plan-card ${plan.popular ? 'popular' : ''} ${isCurrentPlan ? 'current' : ''}">
                        ${plan.popular ? '<div class="plan-badge">Most Popular</div>' : ''}
                        ${isCurrentPlan ? '<div class="plan-badge current">Current Plan</div>' : ''}
                        
                        <div class="plan-header">
                            <h3>${plan.name}</h3>
                            <div class="plan-price">
                                <span class="price">${price}</span>
                                ${plan.price > 0 ? '<span class="period">/month</span>' : ''}
                            </div>
                        </div>
                        
                        <div class="plan-features">
                            <ul>
                                ${plan.features.map(feature => `<li><i class="dashicons dashicons-yes"></i> ${feature}</li>`).join('')}
                            </ul>
                        </div>
                        
                        <div class="plan-action">
                            ${isCurrentPlan ? 
                                '<button class="button button-secondary" disabled>Current Plan</button>' :
                                `<button class="button button-primary wischat-plan-select" data-plan-id="${plan.id}">
                                    ${plan.price > 0 ? 'Subscribe Now' : 'Activate Free Plan'}
                                </button>`
                            }
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            container.html(html);
        }

        renderCurrentSubscription() {
            const container = $('#wischat-current-subscription');
            if (!container.length || !this.currentPlan) return;

            const isActive = this.currentPlan.isActive;
            const statusClass = isActive ? 'active' : 'expired';
            
            let html = `
                <div class="wischat-subscription-status ${statusClass}">
                    <div class="subscription-info">
                        <h3>Current Subscription</h3>
                        <p><strong>Plan:</strong> ${this.currentPlan.planName}</p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge ${statusClass}">
                                ${isActive ? 'Active' : 'Expired'}
                            </span>
                        </p>
                        ${this.currentPlan.endDate ? 
                            `<p><strong>Expires:</strong> ${new Date(this.currentPlan.endDate).toLocaleDateString()}</p>` : 
                            ''
                        }
                    </div>
                    
                    ${this.currentPlan.planId !== 'free' && isActive ? 
                        '<button class="button button-secondary wischat-cancel-subscription">Cancel Subscription</button>' : 
                        ''
                    }
                </div>
            `;

            container.html(html);
        }

        handlePlanSelection(e) {
            e.preventDefault();
            const planId = $(e.target).data('plan-id');
            const plan = this.plans.find(p => p.id === planId);
            
            if (!plan) return;

            if (plan.id === 'free') {
                this.subscribeToPlan(planId);
            } else {
                this.showPaymentForm(plan);
            }
        }

        showPaymentForm(plan) {
            const modal = this.createPaymentModal(plan);
            $('body').append(modal);
            $('#wischat-payment-modal').fadeIn();
        }

        createPaymentModal(plan) {
            return `
                <div id="wischat-payment-modal" class="wischat-modal" style="display: none;">
                    <div class="wischat-modal-content">
                        <div class="wischat-modal-header">
                            <h2>Subscribe to ${plan.name}</h2>
                            <span class="wischat-modal-close">&times;</span>
                        </div>
                        
                        <div class="wischat-modal-body">
                            <div class="plan-summary">
                                <h3>${plan.name}</h3>
                                <p class="plan-price">UGX ${plan.price.toLocaleString()}/month</p>
                                <ul class="plan-features">
                                    ${plan.features.map(feature => `<li>${feature}</li>`).join('')}
                                </ul>
                            </div>
                            
                            <form id="wischat-payment-form">
                                <input type="hidden" name="plan_id" value="${plan.id}">
                                
                                <div class="form-group">
                                    <label for="phone_number">MTN Mobile Money Number</label>
                                    <div class="phone-input-group">
                                        <input type="tel" 
                                               id="phone_number" 
                                               name="phone_number" 
                                               placeholder="256701234567" 
                                               required>
                                        <button type="button" class="button button-secondary wischat-validate-phone">
                                            Validate
                                        </button>
                                    </div>
                                    <p class="description">
                                        Enter your MTN Mobile Money number (including country code)
                                    </p>
                                    <div id="phone-validation-result"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="terms_accepted" required>
                                        I agree to the <a href="#" target="_blank">Terms of Service</a> 
                                        and <a href="#" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                                
                                <div class="payment-info">
                                    <h4>Payment Process:</h4>
                                    <ol>
                                        <li>Click "Pay Now" to initiate payment</li>
                                        <li>You'll receive an SMS prompt on your phone</li>
                                        <li>Enter your MTN Mobile Money PIN to complete payment</li>
                                        <li>Your subscription will be activated automatically</li>
                                    </ol>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="button button-secondary wischat-modal-close">
                                        Cancel
                                    </button>
                                    <button type="submit" class="button button-primary">
                                        Pay UGX ${plan.price.toLocaleString()}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
        }

        async validatePhoneNumber(e) {
            e.preventDefault();
            const phoneNumber = $('#phone_number').val();
            const resultDiv = $('#phone-validation-result');
            const button = $(e.target);
            
            if (!phoneNumber) {
                resultDiv.html('<p class="error">Please enter a phone number</p>');
                return;
            }

            button.prop('disabled', true).text('Validating...');

            try {
                const response = await this.apiCall('/api/v1/payments/validate-account', 'POST', {
                    phoneNumber: phoneNumber
                });

                if (response.success && response.isActive) {
                    resultDiv.html('<p class="success"><i class="dashicons dashicons-yes"></i> Valid MTN Mobile Money account</p>');
                } else {
                    resultDiv.html('<p class="error"><i class="dashicons dashicons-no"></i> Invalid or inactive MTN Mobile Money account</p>');
                }
            } catch (error) {
                resultDiv.html('<p class="error">Failed to validate account. Please try again.</p>');
            } finally {
                button.prop('disabled', false).text('Validate');
            }
        }

        async handlePaymentSubmission(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            const submitButton = $(e.target).find('button[type="submit"]');
            submitButton.prop('disabled', true).text('Processing...');

            try {
                const response = await this.apiCall('/api/v1/payments/subscribe', 'POST', {
                    planId: data.plan_id,
                    phoneNumber: data.phone_number
                });

                if (response.success) {
                    this.showPaymentProgress(response);
                } else {
                    this.showError(response.message || 'Payment failed');
                    submitButton.prop('disabled', false).text('Pay Now');
                }
            } catch (error) {
                this.showError('Payment processing failed. Please try again.');
                submitButton.prop('disabled', false).text('Pay Now');
            }
        }

        showPaymentProgress(paymentData) {
            $('#wischat-payment-modal').fadeOut(() => {
                const progressModal = this.createProgressModal(paymentData);
                $('body').append(progressModal);
                $('#wischat-progress-modal').fadeIn();
                
                // Start polling for payment status
                this.pollPaymentStatus(paymentData.referenceId);
            });
        }

        createProgressModal(paymentData) {
            return `
                <div id="wischat-progress-modal" class="wischat-modal" style="display: none;">
                    <div class="wischat-modal-content">
                        <div class="wischat-modal-header">
                            <h2>Payment in Progress</h2>
                        </div>
                        
                        <div class="wischat-modal-body">
                            <div class="payment-progress">
                                <div class="progress-icon">
                                    <i class="dashicons dashicons-smartphone"></i>
                                </div>
                                
                                <h3>Check Your Phone</h3>
                                <p>${paymentData.instructions}</p>
                                
                                <div class="progress-steps">
                                    <div class="step active">
                                        <i class="dashicons dashicons-yes"></i>
                                        Payment request sent
                                    </div>
                                    <div class="step pending" id="step-phone-prompt">
                                        <i class="dashicons dashicons-clock"></i>
                                        Waiting for phone confirmation
                                    </div>
                                    <div class="step pending" id="step-completion">
                                        <i class="dashicons dashicons-clock"></i>
                                        Payment completion
                                    </div>
                                </div>
                                
                                <div id="payment-status-message">
                                    <p>Please complete the payment on your phone...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        async pollPaymentStatus(referenceId) {
            const maxAttempts = 60; // 5 minutes (5 second intervals)
            let attempts = 0;

            const poll = async () => {
                try {
                    const response = await this.apiCall(`/api/v1/payments/status/${referenceId}`, 'GET');
                    
                    if (response.success) {
                        if (response.status === 'SUCCESSFUL') {
                            this.showPaymentSuccess();
                            this.loadSubscriptionData(); // Refresh subscription data
                            return;
                        } else if (response.status === 'FAILED') {
                            this.showPaymentFailure(response.message);
                            return;
                        }
                    }

                    attempts++;
                    if (attempts < maxAttempts) {
                        setTimeout(poll, 5000); // Poll every 5 seconds
                    } else {
                        this.showPaymentTimeout();
                    }
                } catch (error) {
                    console.error('Payment status check failed:', error);
                    attempts++;
                    if (attempts < maxAttempts) {
                        setTimeout(poll, 5000);
                    } else {
                        this.showPaymentTimeout();
                    }
                }
            };

            poll();
        }

        showPaymentSuccess() {
            $('#payment-status-message').html(`
                <div class="success-message">
                    <i class="dashicons dashicons-yes-alt"></i>
                    <h3>Payment Successful!</h3>
                    <p>Your subscription has been activated. You can now enjoy all the features of your plan.</p>
                    <button class="button button-primary" onclick="location.reload()">Continue</button>
                </div>
            `);
            
            $('#step-phone-prompt, #step-completion').removeClass('pending').addClass('active');
        }

        showPaymentFailure(message) {
            $('#payment-status-message').html(`
                <div class="error-message">
                    <i class="dashicons dashicons-dismiss"></i>
                    <h3>Payment Failed</h3>
                    <p>${message || 'The payment could not be completed. Please try again.'}</p>
                    <button class="button button-primary wischat-modal-close">Try Again</button>
                </div>
            `);
        }

        showPaymentTimeout() {
            $('#payment-status-message').html(`
                <div class="warning-message">
                    <i class="dashicons dashicons-clock"></i>
                    <h3>Payment Timeout</h3>
                    <p>The payment is taking longer than expected. Please check your subscription status or contact support.</p>
                    <button class="button button-primary" onclick="location.reload()">Refresh Page</button>
                </div>
            `);
        }

        async subscribeToPlan(planId) {
            try {
                const response = await this.apiCall('/api/v1/payments/subscribe', 'POST', {
                    planId: planId
                });

                if (response.success) {
                    this.showSuccess('Free plan activated successfully!');
                    this.loadSubscriptionData();
                } else {
                    this.showError(response.message || 'Failed to activate plan');
                }
            } catch (error) {
                this.showError('Failed to activate plan. Please try again.');
            }
        }

        async handleCancelSubscription(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.')) {
                return;
            }

            try {
                const response = await this.apiCall('/api/v1/payments/cancel-subscription', 'POST');
                
                if (response.success) {
                    this.showSuccess('Subscription cancelled successfully');
                    this.loadSubscriptionData();
                } else {
                    this.showError(response.message || 'Failed to cancel subscription');
                }
            } catch (error) {
                this.showError('Failed to cancel subscription. Please try again.');
            }
        }

        async apiCall(endpoint, method = 'GET', data = null) {
            const url = `${this.apiEndpoint}${endpoint}`;
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiKey}`
                }
            };

            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            return await response.json();
        }

        showSuccess(message) {
            this.showNotice(message, 'success');
        }

        showError(message) {
            this.showNotice(message, 'error');
        }

        showNotice(message, type) {
            const notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            $('.wrap h1').after(notice);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                notice.fadeOut();
            }, 5000);
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        if (typeof wischat_admin !== 'undefined') {
            new WisChatSubscriptionManager();
        }

        // Modal close handlers
        $(document).on('click', '.wischat-modal-close, .wischat-modal', function(e) {
            if (e.target === this) {
                $('.wischat-modal').fadeOut(() => {
                    $('.wischat-modal').remove();
                });
            }
        });
    });

})(jQuery);
