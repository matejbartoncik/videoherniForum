<?php
require_once __DIR__.'/../core/session.php';
require_once __DIR__.'/../core/auth.php';
require_once __DIR__.'/../core/csrf.php';

$pageTitle = 'Registrace';

// Handle form submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser($_POST, $_FILES);
    if ($result['success']) {
        // Redirect after successful registration
        header('Location:/?page=login');
        exit;
    } else {
        // Store errors to display the user
        $errors = $result['errors'];
    }
}
?>

<link rel="stylesheet" href="../public/assets/style/register.css">

<div class="register-wrapper">
    <form class="register-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token(),ENT_QUOTES)?>">

        <h1>Registrace</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-box" style="color:red; margin-bottom:10px;">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>

        <div class="avatar-wrapper">
            
                <img id="avatarPreview" class="" src="https://avatar.iran.liara.run/public" style="width: 200px; height: 200px; object-fit: cover; border-radius: 50%;" />
                <button type="button" class="upload-btn" id="uploadBtn">Nahrát obrázek</button>
            </div>

            <input type="file" id="photoInput" accept="image/*" style="display:none;">
            <input type="hidden" name="photo_blob" id="photoBlob">

        <div class="form-group">
            <label>Přezdívka</label>
            <input type="text" name="nickname" placeholder="Vaše přezdívka" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Heslo</label>
                <input type="password" name="password" placeholder="Vaše heslo" required>
            </div>
            <div class="form-group">
                <label>Heslo - kontrola</label>
                <input type="password" name="password_confirm" placeholder="Heslo znovu" required>
                <span class="error-message" style="color:red; font-size:0.9rem; display:none;"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Jméno</label>
                <input type="text" name="firstname" placeholder="Vaše jméno" required>
            </div>
            <div class="form-group">
                <label>Příjmení</label>
                <input type="text" name="lastname" placeholder="Vaše příjmení" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Váš email" required>
            </div>
            <div class="form-group">
                <label>Telefon</label>
                <input type="tel" name="phone" placeholder="Vaše telefonní číslo" required>
            </div>
        </div>

        <button type="submit" class="register-btn">Zaregistrovat se</button>
    </form>

    <script>
            const form = document.querySelector(".register-form");
            const password = form.querySelector("input[name='password']");
            const passwordConfirm = form.querySelector("input[name='password_confirm']");
            const errorMessage = passwordConfirm.nextElementSibling;
            
            // Profile picture upload functionality
            const uploadBtn = document.getElementById('uploadBtn');
            const photoInput = document.getElementById('photoInput');
            const avatarPreview = document.getElementById('avatarPreview');
            const photoBlob = document.getElementById('photoBlob');
            
            uploadBtn.addEventListener('click', function() {
                photoInput.click();
            });
            
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.onload = function() {
                            // Create canvas for 200x200 crop
                            const canvas = document.createElement('canvas');
                            canvas.width = 200;
                            canvas.height = 200;
                            const ctx = canvas.getContext('2d');
                            
                            // Calculate dimensions to crop to square
                            const size = Math.min(img.width, img.height);
                            const x = (img.width - size) / 2;
                            const y = (img.height - size) / 2;
                            
                            // Draw cropped and resized image
                            ctx.drawImage(img, x, y, size, size, 0, 0, 200, 200);
                            
                            // Update preview
                            avatarPreview.src = canvas.toDataURL('image/jpeg', 0.9);
                            
                            // Convert to base64 for form submission
                            photoBlob.value = canvas.toDataURL('image/jpeg', 0.9);
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            form.addEventListener("submit", function(e) {
                if (password.value !== passwordConfirm.value) {
                    e.preventDefault();
                    errorMessage.textContent = "Hesla se musí shodovat!";
                    errorMessage.style.display = "block";
                } else {
                    errorMessage.style.display = "none";
                }
            });
        </script>
</div>
