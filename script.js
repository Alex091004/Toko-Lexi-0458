document.addEventListener('DOMContentLoaded', () => {

    // ========== Register Form Validation ==========
    const registerForm = document.querySelector('form#registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const pw = registerForm.password.value.trim();
            const pwConfirm = registerForm.password_confirm.value.trim();
            const phone = registerForm.no_telp.value.trim();
            const errorBox = document.querySelector('#registerError');

            let errors = [];

            // Check password match
            if (pw !== pwConfirm) {
                errors.push("Password dan konfirmasi tidak cocok.");
            }

            // Check password length
            if (pw.length < 6) {
                errors.push("Password minimal 6 karakter.");
            }

            // Check phone numeric
            if (!/^\d+$/.test(phone)) {
                errors.push("Nomor telepon harus berupa angka.");
            }

            // Show errors if any
            if (errors.length > 0) {
                e.preventDefault();
                if (errorBox) {
                    errorBox.innerHTML = errors.map(err => `<div class="text-danger">${err}</div>`).join('');
                } else {
                    alert(errors.join("\n"));
                }
            }
        });
    }

    // ========== Optional: Password Toggle ==========
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(`#${btn.dataset.target}`);
            if (input) {
                input.type = input.type === "password" ? "text" : "password";
                btn.innerText = input.type === "password" ? "👁️" : "🙈";
            }
        });
    });

    // ========== Optional: Auto-hide success messages ==========
    const alertSuccess = document.querySelector('.alert-success');
    if (alertSuccess) {
        setTimeout(() => {
            alertSuccess.style.display = 'none';
        }, 4000);
    }

});
