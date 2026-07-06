<?php
session_start();
if (isset($_SESSION['id_utilisateur'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = "";
$field_error = "";

if (isset($_GET['error'])) {
    $error_type = $_GET['error'];
    if ($error_type === 'incomplet') {
        $error_message = "Veuillez remplir le champ.";
    } elseif ($error_type === 'email_tsy_misy') {
        $error_message = "Cet email sans utilisée.";
        $field_error = "email";
    } elseif ($error_type === 'password_diso') {
        $error_message = "Votre mot de passe est incorrecte.";
        $field_error = "password";
    } else {
        $error_message = htmlspecialchars($_GET['error']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — RégieBudget</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #FFFFFF;
            width: 100%;
            max-width: 420px;
            padding: 44px 38px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        }
        .login-header { text-align: center; margin-bottom: 32px; }
        .login-header h1 { font-size: 28px; font-weight: 700; color: #0A2540; margin-bottom: 8px; }
        .login-header p { font-size: 15px; color: #627D98; }
        .form-group { margin-bottom: 22px; position: relative; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #334E68; margin-bottom: 8px; }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #DCE2E7;
            border-radius: 12px;
            font-size: 15px;
            color: #102A43;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-group input:focus { border-color: #0A2540; box-shadow: 0 0 0 4px rgba(10, 37, 64, 0.05); }
        .form-group input.error { border-color: #D32F2F; background: #FFFBFA; }
        .field-error { color: #D32F2F; font-size: 13px; font-weight: 500; margin-top: 6px; display: block; }
        .error-message {
            background: #FFFBFA;
            border: 1.5px solid #FFD0CC;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            color: #D32F2F;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-login {
            width: 100%;
            padding: 16px;
            background: #D32F2F;
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .btn-login:hover { background: #001020; }
        .form-footer { text-align: center; margin-top: 32px; }
        .form-footer p { font-size: 13px; color: #829AB1; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h1>RégieBudget</h1>
        <p>Université ONIFRA — Identification</p>
    </div>
    
    <?php if (!empty($error_message)): ?>
    <div class="error-message">
        <span>⚠️ <?php echo $error_message; ?></span>
    </div>
    <?php endif; ?>
    
    <form action="../actions/se_connecter.php" method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="exemple@email.com" 
                   value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                   class="<?php echo ($field_error === 'email') ? 'error' : ''; ?>" required>
            <?php if ($field_error === 'email'): ?>
                <span class="field-error">Cet email n'existe pas</span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" placeholder="Entrez votre mot de passe" 
                   class="<?php echo ($field_error === 'password') ? 'error' : ''; ?>" required>
            <?php if ($field_error === 'password'): ?>
                <span class="field-error">Mot de passe incorrecte</span>
            <?php endif; ?>
        </div>
        
        <button type="submit" name="connexion" class="btn-login">Se connecter</button>
    </form>
    
    <div class="form-footer">
        <p>©RégieBudget — Tous droits réservés</p>
    </div>
</div>
</body>
</html>