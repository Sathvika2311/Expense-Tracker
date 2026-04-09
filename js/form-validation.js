// Registration Form Validation
function validateRegistrationForm() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    if (!email.value || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email.value)) {
        showError('Please enter a valid email address.', email);
        return false;
    }
    if (!password.value || password.value.length < 6) {
        showError('Password must be at least 6 characters.', password);
        return false;
    }
    return true;
}

// Login Form Validation
function validateLoginForm() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    if (!email.value) {
        showError('Email is required.', email);
        return false;
    }
    if (!password.value) {
        showError('Password is required.', password);
        return false;
    }
    return true;
}

// Expense/Income Form Validation
function validateTransactionForm(formId) {
    const form = document.getElementById(formId);
    const amount = form.querySelector('[name="amount"]');
    const date = form.querySelector('[name="date"]');
    const category = formId === 'expenseForm' ? form.querySelector('[name="category"]') : form.querySelector('[name="source"]');
    
    if (!category || !category.value) {
        showError('Please select a category.', category);
        return false;
    }
    
    if (!amount.value || isNaN(amount.value) || Number(amount.value) <= 0) {
        showError('Please enter a valid amount.', amount);
        return false;
    }
    
    if (!date.value) {
        showError('Date is required.', date);
        return false;
    }
    
    return true;
}

// Budget Form Validation
function validateBudgetForm() {
    const category = document.getElementById('category');
    const amount = document.getElementById('amount');
    const month = document.getElementById('month');
    const year = document.getElementById('year');
    
    if (!category.value) {
        showError('Please select a category.', category);
        return false;
    }
    
    if (!amount.value || isNaN(amount.value) || Number(amount.value) <= 0) {
        showError('Please enter a valid budget amount.', amount);
        return false;
    }
    
    if (!month.value) {
        showError('Please select a month.', month);
        return false;
    }
    
    if (!year.value) {
        showError('Please select a year.', year);
        return false;
    }
    
    return true;
}

// Savings Goal Form Validation
function validateSavingsForm() {
    const title = document.getElementById('title');
    const targetAmount = document.getElementById('target_amount');
    const currentAmount = document.getElementById('current_amount');
    
    if (!title.value || title.value.trim().length < 3) {
        showError('Please enter a goal title (at least 3 characters).', title);
        return false;
    }
    
    if (!targetAmount.value || isNaN(targetAmount.value) || Number(targetAmount.value) <= 0) {
        showError('Please enter a valid target amount.', targetAmount);
        return false;
    }
    
    if (currentAmount.value && (isNaN(currentAmount.value) || Number(currentAmount.value) < 0)) {
        showError('Current amount must be a valid number.', currentAmount);
        return false;
    }
    
    return true;
}

// Show error message with enhanced UI feedback
function showError(message, inputElement) {
    // Remove any existing error messages
    const existingErrors = document.querySelectorAll('.error-message');
    existingErrors.forEach(error => error.remove());
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.9rem';
    errorDiv.style.marginTop = '5px';
    errorDiv.style.animation = 'fadeIn 0.3s ease';
    errorDiv.textContent = message;
    
    // Add error styling to input
    if (inputElement) {
        inputElement.style.borderColor = '#ef4444';
        inputElement.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
        inputElement.style.animation = 'shake 0.5s ease';
        
        // Insert error message after input
        inputElement.parentNode.insertBefore(errorDiv, inputElement.nextSibling);
        
        // Focus on the input
        inputElement.focus();
        
        // Remove error styling when input changes
        inputElement.addEventListener('input', function() {
            this.style.borderColor = '';
            this.style.backgroundColor = '';
            const errorMsg = this.parentNode.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }, { once: true });
    } else {
        // If no input element is provided, show alert
        alert(message);
    }
}

// Add keyframe animations for error feedback
function addErrorAnimations() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
}

// Attach event listeners if forms exist
document.addEventListener('DOMContentLoaded', function() {
    // Add animations
    addErrorAnimations();
    
    // Registration form
    const regForm = document.getElementById('registerForm');
    if (regForm) regForm.onsubmit = function() { return validateRegistrationForm(); };
    
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) loginForm.onsubmit = function() { return validateLoginForm(); };
    
    // Expense form
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) expenseForm.onsubmit = function() { return validateTransactionForm('expenseForm'); };
    
    // Income form
    const incomeForm = document.getElementById('incomeForm');
    if (incomeForm) incomeForm.onsubmit = function() { return validateTransactionForm('incomeForm'); };
    
    // Budget form
    const budgetForm = document.querySelector('form[action*="budget.php"]');
    if (budgetForm) budgetForm.onsubmit = function() { return validateBudgetForm(); };
    
    // Savings form
    const savingsForm = document.querySelector('form[action*="savings.php"]');
    if (savingsForm && savingsForm.querySelector('[name="action"][value="create"]')) {
        savingsForm.onsubmit = function() { return validateSavingsForm(); };
    }
}); 