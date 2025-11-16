<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Cards Demo - HealthSure</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container" style="padding: 2rem 0;">
        <h1 class="text-center mb-5">Enhanced Pricing Cards Demo</h1>
        
        <!-- Pricing Cards Grid -->
        <div class="row">
            <!-- Basic Plan -->
            <div class="col-4">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <div class="pricing-amount">
                            <span class="currency">₹</span>25,000<span class="decimal">.00</span>
                        </div>
                        <div class="pricing-period">Annual Premium</div>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li>
                                <span class="feature-name">Coverage Amount</span>
                                <span class="feature-value">₹5,00,000</span>
                            </li>
                            <li>
                                <span class="feature-name">Cashless Limit</span>
                                <span class="feature-value">₹3,00,000</span>
                            </li>
                            <li>
                                <span class="feature-name">Network Hospitals</span>
                                <span class="feature-value">500+</span>
                            </li>
                            <li>
                                <span class="feature-name">Pre-existing Conditions</span>
                                <span class="feature-value">After 2 Years</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <button class="btn btn-primary pricing-btn">Choose Basic Plan</button>
                    </div>
                </div>
            </div>
            
            <!-- Premium Plan (Featured) -->
            <div class="col-4">
                <div class="pricing-card featured">
                    <div class="pricing-header">
                        <div class="pricing-amount large-number">
                            <span class="currency">₹</span>125,000<span class="decimal">.00</span>
                        </div>
                        <div class="pricing-period">Monthly Premium</div>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li>
                                <span class="feature-name">Coverage Amount</span>
                                <span class="feature-value">₹25,00,000</span>
                            </li>
                            <li>
                                <span class="feature-name">Cashless Limit</span>
                                <span class="feature-value">₹20,00,000</span>
                            </li>
                            <li>
                                <span class="feature-name">Network Hospitals</span>
                                <span class="feature-value">1000+</span>
                            </li>
                            <li>
                                <span class="feature-name">Pre-existing Conditions</span>
                                <span class="feature-value">Immediate</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <button class="btn btn-primary pricing-btn">Choose Premium Plan</button>
                    </div>
                </div>
            </div>
            
            <!-- Family Plan -->
            <div class="col-4">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <div class="pricing-amount">
                            <span class="currency">₹</span>75,000<span class="decimal">.00</span>
                        </div>
                        <div class="pricing-period">Annual Premium</div>
                    </div>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                            <li>
                                <span class="feature-name">Coverage Amount</span>
                                <span class="feature-value">₹15,00,000</span>
                            </li>
                            <li>
                                <span class="feature-name">Family Members</span>
                                <span class="feature-value">Up to 6</span>
                            </li>
                            <li>
                                <span class="feature-name">Maternity Cover</span>
                                <span class="feature-value">Included</span>
                            </li>
                            <li>
                                <span class="feature-name">Dependent Age Limit</span>
                                <span class="feature-value">25 Years</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <button class="btn btn-primary pricing-btn">Choose Family Plan</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Amount Display Demo -->
        <div class="row mt-5">
            <div class="col-6">
                <h3>Payment Amount Display</h3>
                <div class="payment-amount-display">
                    <div class="amount">₹125,000.00</div>
                    <div class="label">Monthly Premium</div>
                </div>
            </div>
            
            <div class="col-6">
                <h3>Financial Summary</h3>
                <div class="financial-summary">
                    <div class="financial-item">
                        <div class="value">₹125,000</div>
                        <div class="label">Premium Amount</div>
                    </div>
                    <div class="financial-item">
                        <div class="value">₹25,00,000</div>
                        <div class="label">Coverage</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Policy Cards -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>Enhanced Policy Cards</h3>
            </div>
            <div class="col-4 policy-card">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Health Shield Pro</h4>
                            <span class="badge badge-primary">Health</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-light">Comprehensive health insurance with extensive coverage and benefits.</p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Coverage Amount:</span>
                                <strong>₹25,00,000</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Annual Premium:</span>
                                <strong>₹125,000</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Policy Term:</span>
                                <strong>5 Year(s)</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" style="width: 100%;">Apply Now</button>
                    </div>
                </div>
            </div>
            
            <div class="col-4 policy-card">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Life Secure Plus</h4>
                            <span class="badge badge-success">Life</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-light">Complete life insurance protection for you and your family's future.</p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Coverage Amount:</span>
                                <strong>₹50,00,000</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Annual Premium:</span>
                                <strong>₹85,000</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Policy Term:</span>
                                <strong>20 Year(s)</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" style="width: 100%;">Apply Now</button>
                    </div>
                </div>
            </div>
            
            <div class="col-4 policy-card">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Family Care Complete</h4>
                            <span class="badge badge-warning">Family</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-light">Comprehensive family insurance with maternity and dependent coverage.</p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Coverage Amount:</span>
                                <strong>₹15,00,000</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Annual Premium:</span>
                                <strong>₹75,000</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Policy Term:</span>
                                <strong>3 Year(s)</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" style="width: 100%;">Apply Now</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="landing.php" class="btn btn-outline">← Back to Landing Page</a>
            <a href="customer/browse-policies.php" class="btn btn-primary">Browse All Policies</a>
        </div>
    </div>
    
    <script>
        // Auto-detect and handle large pricing amounts
        document.addEventListener('DOMContentLoaded', function() {
            const pricingAmounts = document.querySelectorAll('.pricing-amount');
            
            pricingAmounts.forEach(function(element) {
                const text = element.textContent.replace(/[₹,.\s]/g, '');
                const number = parseInt(text);
                
                // If the number is large (6+ digits), apply large-number class
                if (number >= 100000) {
                    element.classList.add('large-number');
                }
            });
        });
    </script>
</body>
</html>
